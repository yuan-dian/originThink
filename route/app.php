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

/**
 * 免权限验证路由
 */
Route::group('admin', [
    'login$'=>'Login/login',                                         //登录
    'editPassword'=>'User/editPassword',                             //重置密码
    'logout$'=>'Login/logout',                                       //退出
    'check$'=>'User/check',                                          //验证用户是否存在
    'unlock'=>'Login/unlock',                                        //验证用户是否存在
    'verify'=>'Login/verify',                                        //获取验证码
])->ext('html');
/**
 * 需要权限验证路由
 */
Route::group('admin', [

    //首页
    'index$'=>'Index/index',                                           //首页
    'home'=>'Index/home',                                              //系统信息

    //用户管理
    'userList$'=>'User/userList',                                      //用户列表
    'userInfo$'=>'User/userInfo',                                      //用户信息
    'edit$'=>'User/edit',                                              //添加/编辑用户
    'delete$'=>'User/delete',                                          //删除用户
    'groupList$'=>'User/groupList',                                    //用户组列表
    'editGroup$'=>'User/editGroup',                                    //添加编辑用户组
    'disableGroup$'=>'User/disableGroup',                              //禁用用户组
    'ruleList$'=>'User/ruleList',                                      //用户组规则列表
    'editRule$'=>'User/editRule',                                      //修改用户组规则

    //系统管理
    'cleanCache$'=>'System/cleanCache',                                //清除缓存
    'log$'=>'System/loginLog',                                         //登录日志
    'downlog$'=>'System/downLoginLog',                                 //下载登录日志
    'menu$'=>'System/menu',                                            //系统菜单
    'editMenu$'=>'System/editMenu',                                    //编辑菜单
    'deleteMenu$'=>'System/deleteMenu',                                //删除菜单
    'config'=>'System/config',                                         //系统配置
    'siteConfig'=>'System/siteConfig',                                 //站点配置
    'noticeConfig'=>'System/noticeConfig',                             //公告配置
    //上传管理
    'upload'=>'admin/Upload/index',                                    //上传图片
])->middleware(app\middleware\CheckAuth::class)->ext('html');          //使用中间件验证

Route::group('admin', function (){
    Route::rule('index$', 'Index/index');
    Route::rule('home', 'Index/home');
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

Route::group('admin', function (){
    Route::rule('login$', 'Login/login');
})->ext('html');
/**
 * miss路由
 * 没有定义的路由全部使用该路由
 */
Route::miss('Login/login');
