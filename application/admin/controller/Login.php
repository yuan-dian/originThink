<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/16
 * Time: 15:24
 */

namespace app\admin\controller;
use think\Controller;
class Login extends Controller
{
    /**
     * 用户登录
     * @return mixed
     * @author 原点 <467490186@qq.com>
     */
    public function aa()
    {
//        if(get_user_id()){
//            $this->redirect(url('/admin/index'));
//        }else{
        if(!request()->isPost()){
            return $this->fetch();
        }else{
            $data=input();
            $result=model('User')->login($data);
            return $result;
        }
//        }

    }
    /**
     * 用户登录
     * @return mixed
     * @author 原点 <467490186@qq.com>
     */
    public function login()
    {
//        if(get_user_id()){
//            $this->redirect(url('/admin/index'));
//        }else{
            if(!request()->isPost()){
                return $this->fetch();
            }else{
                $data=input();
                $result=model('User')->login($data);
                return $result;
            }
//        }

    }
    /**
     * 用户退出
     * @return array
     * @author 原点 <467490186@qq.com>
     */
    public function logout()
    {
        session('user_auth', null);
        session('user_auth_sign', null);
        return ['msg'=>'退出成功','url'=>url('/admin/login')];
    }
    /**
     * 重置密码
     * @return mixed
     * @author 原点 <467490186@qq.com>
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function resetpassword()
    {
        if(request()->isPost()){
            $data=input();
            $list=model('User')->where(['uid'=>get_user_id()])->find();
            if($list['password']!=md5(md5($data['oldpassword']).$list['random'])){
                $this->error('原密码错误');
            }else{
                $random=rand(1111,9999);
                $save=[
                    'password'=>md5(md5($data['password']).$random),
                    'random'=>$random,
                    'updatapassword'=>1,
                ];
                $res=model('User')->isUpdate(true)->save($save,['uid'=>get_user_id()]);
                if($res){
                    $this->success('重置成功,请重新登录',url('/admin/login'));
                }else{
                    $this->error('修改失败');
                }
            }
        }else{
            return $this->fetch();
        }
    }

}