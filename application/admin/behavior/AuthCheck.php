<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/19
 * Time: 10:08
 */

namespace app\admin\behavior;
use think\Controller;
class AuthCheck extends Controller
{
    protected $uid;
    protected $group_id;

    
    public function run()
    {
        $this->checkLogin();//验证是否登录
        $this->check();     //验证权限
    }

    /**
     * 验证是否登录
     */
    private function checkLogin()
    {
        $user = session('user_auth');
        //验证是否登录
        if (!$user || session('user_auth_sign') != sign($user)){
            return   $this->error('登录后查看', url('/admin/login'));
        }
        //验证是否需要重置密码
        if($user['updatapassword']==0){
            return $this->error('重置密码后使用',url('/admin/editPassword') );
        }
        $this->uid=$user['uid'];
        $this->group_id=$user['group_id'];
    }

    /**
     * 验证权限
     */
    private function check(){
        if($this->uid!=1){
            $Auth=new \org\Auth();
            if(!$Auth->check(request()->path(),$this->uid)) { //验证当前访问页面的权限
                alert_error(config('auth.not_auth_tip'));
            }
        }
    }
}