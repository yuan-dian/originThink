<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/18
 * Time: 15:05
 */

namespace app\admin\controller;


use app\admin\service\UserService;
use app\admin\service\AuthGroupService;
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
        $map['user']=input('username','','trim');
        $res=model('User')->where($map)->field('uid')->find();
        if($res){
            $msg=['code'=>1,'msg'=>'账号已存在'];
        }else{
            $msg=['status'=>0,'info'=>'验证通过'];
        }
        return $msg;
    }

    /**
     * 删除用户
     * @author 原点 <467490186@qq.com>
     */
    public function delete()
    {
        $uid=input('uid',0,'intval');
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
        if(request()->isPost()){
            $id=input('id',0,'intval');
            $type=input('type',0,'intval');
            $title=input('title','','trim');
            $status=input('status',0,'intval');
            $rules=input('rules',[]);
            switch ($type){
                case 1://编辑、添加用户组
                    if($id){//编辑用户组
                        return AuthGroupService::edit($id,['title'=>$title]);
                    }else{//添加用户组
                        return AuthGroupService::add($title);
                    }
                    break;
                case 2://是否禁用用户组
                    return AuthGroupService::edit($id,['status'=>$status]);
                    break;
                case 3://获取权限列表
                    $list=db('auth_rule')->field('id,pid,title as text')->select();
                    $data=list_to_tree($list,'id','pid','children');
                    return $data;
                    break;
                case 4://修改用户组权限
                    if(!$rules)$this->error('参数错误');
                    sort($rules);
                    $rules=implode(',',$rules);
                    $res=AuthGroupService::edit($id,['rules'=>$rules]);
                    if($res['code']){
                        Cache::clear(config('auth.cache_tag'));//清除Auth类设置的缓存
                    }
                    return $res;
                    break;
            }
        }else{
            $list=[];
            if(request()->isAjax()){
                $key=input('key','','trim');
                $limit=input('key',10,'intval');
                $map=[];
                empty($key) || $map[]=['title','like','%'.$key.'%'];
                $list=model('AuthGroup')->where($map)->paginate($limit,false,['query'=>['key'=>$key],'limit'=>$limit]);
                $data=$list->toarray();
                return (['code'=>0,'mag'=>'','data'=>$data['data'],'count'=>$data['total']]);
            }
            $this->assign('list',$list);
            return $this->fetch();
        }
    }

    /**
     * 修改密码
     * @return array|mixed
     * @author 原点 <467490186@qq.com>
     * @throws \think\Exception\DbException
     */
    public function editPassword()
    {
        if(request()->isPost()){
            $data=input();
            $uid=$this->uid;
            $res=UserService::editPassword($uid,$data['oldpassword'],$data['password']);
            return $res;
        }else{
            return $this->fetch();
        }
    }
}