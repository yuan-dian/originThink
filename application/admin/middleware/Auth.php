<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2018/9/5
 * Time: 17:20
 */

namespace app\admin\middleware;

class Auth
{
    public function handle($request, \Closure $next)
    {
        $user = session('user_auth');//获取当前登录信息
        if (!$user || session('user_auth_sign') != sign($user)){ //验证是否登录
            alert_error('登录后查看',url('/admin/login'));
        }
        if ( $user['updatapassword'] == 0 ){ //验证是否需要重置密码
            alert_error('重置密码后使用',url('/admin/editPassword'));
        }
        if ( $user['uid'] != 1 ){ //判断是否是超级管理员
            $Auth = new \org\Auth($user['uid'],$user['group_id']);
            if ( !$Auth->check($request->path()) ) { //验证当前访问页面的权限
                alert_error(config('auth.not_auth_tip'));
            }
        }
        return $next($request);
    }
}