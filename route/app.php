<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

Route::get('think', function () {
    return 'hello,ThinkPHP6!';
});

Route::get('hello/:name', 'index/hello');

Route::group('admin', function () {
    Route::rule('index$', 'Index/index');
    Route::rule('home$', 'Index/home');

    Route::rule('userList$', 'User/userList');
    Route::rule('userInfo$', 'User/userInfo');
    Route::rule('edit$', 'User/edit');
    Route::rule('delete$', 'User/delete');
    Route::rule('groupList$', 'User/groupList');
    Route::rule('editGroup$', 'User/editGroup');
    Route::rule('disableGroup$', 'User/disableGroup');
    Route::rule('ruleList$', 'User/ruleList');
    Route::rule('editRule$', 'User/editRule');

    //系统管理
    Route::rule('cleanCache$', 'System/cleanCache');
    Route::rule('log$', 'System/loginLog');     //清除缓存
    Route::rule('downlog$', 'System/downLoginLog');     //清除缓存
    Route::rule('menu$', 'System/menu');     //清除缓存
    Route::rule('editMenu$', 'System/editMenu');     //清除缓存
    Route::rule('deleteMenu$', 'System/deleteMenu');     //清除缓存
    Route::rule('config$', 'System/config');     //清除缓存
    Route::rule('siteConfig', 'System/siteConfig');     //清除缓存
    Route::rule('noticeConfig', 'System/noticeConfig');     //清除缓存
})->middleware(app\middleware\CheckAuth::class)->ext('html');

Route::group('admin', function () {
    Route::rule('login$', 'Login/login');
    //重置密码
    Route::rule('editPassword', 'User/editPassword');
    //退出
    Route::rule('logout$', 'Login/logout');
    //验证用户是否存在
    Route::rule('check$', 'User/check');
    // 解锁
    Route::rule('unlock', 'Login/unlock');
    //获取验证码
    Route::rule('verify', 'Login/verify');
})->ext('html');
/**
 * miss路由
 * 没有定义的路由全部使用该路由
 */
Route::miss('Login/login');
