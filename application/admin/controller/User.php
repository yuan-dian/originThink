<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/18
 * Time: 15:05
 */

namespace app\admin\controller;


use app\admin\service\UserService;
use think\facade\Cache;
class User extends Common
{
    /**
     * 用户列表
     * @return mixed
     * @author 原点 <467490186@qq.com>
     */
    public function userList()
    {
        $list=[];
        if(request()->isAjax()){
            $data=input();
            $map=[];
            empty($data['key']) || $map[]=['user|name','like','%'.$data['key'].'%'];
            isset($data['limit'])?$limit=$data['limit'] : $limit=10;
            $list=model('User')->with(['groupIds'])
                ->where($map)
                ->paginate($limit,false,['query'=>$data]);
            $data=$list->toarray();
            $auth_group=model('auth_group')->column('title','id');
            foreach ($data['data'] as $key=>$val){
                $title=[];
                foreach ($val['group_ids'] as $v){
                    $title[]=$auth_group[$v['group_id']];
                }
                unset( $data['data'][$key]['group_ids']);
                $data['data'][$key]['title']=implode(',',$title);
            }
            return (['code'=>0,'mag'=>'','data'=>$data['data'],'count'=>$data['total']]);
        }
        $this->assign('list',$list);
        return $this->fetch();
    }

    /**
     * 添加、编辑用户
     * @return mixed
     * @author 原点 <467490186@qq.com>
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function edit()
    {
        $data=input();
        if(request()->isPost()){
            if($data['uid']){
                //编辑
                $res=UserService::edit($data);
                return $res;
            }else{
                //添加
                $data=UserService::add($data);
                return $data;
            }
        }else{
            if(isset($data['uid'])){
                $list=model('User')->where('uid','=',$data['uid'])->find();
                $list['group_id']=model('auth_group_access')->where('uid','=',$data['uid'])->column('group_id');
                $this->assign('list',$list);
            }
            $grouplist=model('AuthGroup')->select();
            $this->assign('grouplist',$grouplist);
            return $this->fetch();
        }
    }
    /**
     * 验证用户名是否存在
     * @return array
     * @author 原点 <467490186@qq.com>
     */
    public function check()
    {
        $map['user']=input('param');
        $count=model('User')->where($map)->count();
        if($count>0){
            $msg=['status'=>'n','info'=>'账号已存在'];
        }else{
            $msg=['status'=>'y','info'=>'验证通过'];
        }
        return $msg;
    }

    /**
     * 删除用户
     * @author 原点 <467490186@qq.com>
     */
    public function delete()
    {
        $uid=input('uid');
        if($uid){
            if($uid!=1){
                $res=UserService::delete($uid);
                return $res;
            }else{
                $this->error('无法删除超级管理员');
            }
        }else{
            $this->error('参数错误');
        }
    }

    /**
     * 用户组管理
     * @return array|mixed
     * @author 原点 <467490186@qq.com>
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function groupList()
    {
        $data=input();
        if(request()->isPost()){
            switch ($data['type']){
                case 1://编辑、添加用户
                    if($data['id']){
                        $res=model('AuthGroup')->isUpdate(true)->save(['title'=>$data['title']],['id'=>$data['id']]);
                        if($res){
                            $this->success('修改成功');
                        }else{
                            $this->error('修改失败');
                        }
                    }else{
                        $res=model('AuthGroup')->isUpdate(false)->save(['title'=>$data['title']]);
                        if($res){
                            $this->success('添加成功');
                        }else{
                            $this->error('添加失败');
                        }
                    }
                    break;
                case 2://是否禁用用户组
                    $res=model('AuthGroup')->isUpdate(true)->save(['status'=>$data['status']],['id'=>$data['id']]);
                    if($res){
                        $this->success('修改成功');
                    }else{
                        $this->error('修改失败');
                    }
                    break;
                case 3://获取权限列表
                    $list=db('auth_rule')->field('id,pid,title as text')->select();
                    $data=list_to_tree($list,'id','pid','children');
                    return $data;
                    break;
                case 4://修改用户组权限
                    sort($data['rules']);
                    $rules=implode(',',$data['rules']);
                    $res=model('AuthGroup')->isUpdate(true)->save(['rules'=>$rules],['id'=>$data['id']]);
                    if($res){
                        Cache::clear(config('auth.cache_tag'));//清除Auth类设置的缓存
                        $this->success('修改成功');
                    }else{
                        $this->error('修改失败');
                    }
                    break;
            }
        }
        else{
            $list=[];
            if(request()->isAjax()){
                $map=[];
                empty($data['key']) || $map[]=$map[]=['title','like','%'.$data['key'].'%'];
                isset($data['limit'])?$limit=$data['limit'] : $limit=10;
                $list=model('AuthGroup')->where($map)->paginate($limit,false,['query'=>$data]);
                $data=$list->toarray();
                return (['code'=>0,'mag'=>'','data'=>$data['data'],'count'=>$data['total']]);
            }
            $this->assign('list',$list);
            return $this->fetch();
        }
    }
}