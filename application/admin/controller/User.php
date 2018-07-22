<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/18
 * Time: 15:05
 */

namespace app\admin\controller;



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
        if($this->view_path!='layui' or request()->isAjax()){
            $data=input();
            $map=[];
            empty($data['key']) || $map[]=['a.user|a.name','like','%'.$data['key'].'%'];
            $list=model('User')
                ->alias('a')
                ->field('a.*,c.title')
                ->join('auth_group_access b','a.uid = b.uid')
                ->join('auth_group c','b.group_id = c.id')
                ->where($map)
                ->paginate(10,false,['query'=>$data]);
        }
        if($this->view_path=='layui' && request()->isAjax()){
            $data=$list->toarray();
            return (['code'=>0,'mag'=>'','data'=>$data['data'],'count'=>$data['total']]);
        }
        $this->assign('list',$list);
        return $this->return_fetch();
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
                $User=model('User');
                $userdata=[
                    'name'=>$data['name'],
                    'status'=>$data['status'],
                ];
                $res=$User->allowField(true)->isUpdate(true)->save($userdata,['uid'=>$data['uid']]);
                if($res){
                    model('AuthGroupAccess')->where(['uid'=>$data['uid']])->delete();
                    $save=[
                        'uid'=>$data['uid'],
                        'group_id'=>$data['group_id']
                    ];
                    $res2=model('AuthGroupAccess')->isUpdate(false)->save($save);
                    if($res2){
                        $this->success('编辑成功',url('/admin/userlist'));
                    }else{
                        $this->error('编辑失败');
                    }
                }else{
                    $this->error('编辑失败');
                }
            }else{
                $validate =validate('User');
                if(!$validate->scene('add')->check($data)){
                    $validate->getError()!='令牌数据无效'? $token=$this->request->token():$token='';
                    $this->error($validate->getError(),null,['token'=>$token]);
                }
                $User=model('User');
                $random=rand(1111,9999);
                $userdata=[
                    'user'=>$data['user'],
                    'name'=>$data['name'],
                    'status'=>$data['status'],
                    'password'=>md5(md5($data['password']).$random),
                    'random'=>$random,
                ];
                $res=$User->allowField(true)->isUpdate(false)->save($userdata);
                if($res){
                    $save=[
                        'uid'=>$User->uid,
                        'group_id'=>$data['group_id']
                    ];
                    $res2=model('AuthGroupAccess')->isUpdate(false)->save($save);
                    if($res2){
                        $this->success('添加成功',url('/admin/userList'));
                    }else{
                        $this->error('添加失败',null,['token'=>$this->request->token()]);
                    }
                }

            }
        }else{
            if(isset($data['uid'])){
                $list=model('User')->alias('a')->join('auth_group_access b','a.uid = b.uid')->where('a.uid','=',$data['uid'])->find($data['uid']);
                $this->assign('list',$list);
            }
            $grouplist=model('AuthGroup')->select();
            $this->assign('grouplist',$grouplist);
            return $this->return_fetch();
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
        $id=input('uid');
        if($id){
            if($id!=1){
                $res=model('User')->where('uid',$id)->delete();
                if($res){
                    model('AuthGroupAccess')->where('uid',$id)->delete();
                    $this->success('删除成功');
                }else{
                    $this->error('删除失败');
                }
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
                        cache('ruleslist_'.$data['id'],NULL);//将用户组对应的菜单缓存清除
                        $this->success('修改成功');
                    }else{
                        $this->error('修改失败');
                    }
                    break;
            }
        }
        else{
            $list=[];
            if($this->view_path!='layui' or request()->isAjax()){
                $map=[];
                empty($data['key']) || $map[]=$map[]=['title','like','%'.$data['key'].'%'];
                $list=model('AuthGroup')->where($map)->paginate(10,false,['query'=>$data]);
            }
            if($this->view_path=='layui' && request()->isAjax()){
                $data=$list->toarray();
                return (['code'=>0,'mag'=>'','data'=>$data['data'],'count'=>$data['total']]);
            }
            $this->assign('list',$list);
            return $this->return_fetch();
        }
    }
}