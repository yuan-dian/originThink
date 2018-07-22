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
/**
 * 后台管理路由
 */

/**
 * 免权限验证路由
 */
Route::group('admin', [
    'login$'=>'admin/Login/login',                                           //登录
    'resetpassword$'=>'admin/Login/resetpassword',                           //重置密码
    'logout$'=>'admin/Login/logout',                                         //退出
    'check$'=>'admin/User/check',                                            //验证用户是否存在
    'test$'=>'admin/Login/test',                                            //验证用户是否存在
    'getMenu'=>'admin/index/getMenu',                                            //验证用户是否存在
])->ext('html');
/**
 * 需要权限验证路由
 */
Route::group('admin', [
    //首页
    'index$'=>'admin/Index/index',                                           //首页
    'main'=>'admin/Index/main',                                           //首页
    'home'=>'admin/Index/home',                                           //首页

    //用户管理
    'userList$'=>'admin/User/userList',                                      //用户列表
    'edit$'=>'admin/User/edit',                                              //添加/编辑用户
    'delete$'=>'admin/User/delete',                                     //删除用户
    'groupList$'=>'admin/User/groupList',                                    //用户组列表

    //系统管理
    'cleanCache$'=>'admin/System/cleanCache',                                //清除缓存
    'log$'=>'admin/System/loginLog',                                         //登录日志
    'menu$'=>'admin/System/menu',                                            //系统菜单
    'editMenu$'=>'admin/System/editMenu',                                    //编辑菜单
    'deleteMenu$'=>'admin/System/deleteMenu',                                //删除菜单
    'config'=>'admin/System/config',                                         //配置管理

])->before(['\app\admin\behavior\AuthCheck'])->ext('html');
/**
 * swoole服务
 */
Route::rule('swoole/start','admin/Swoole/start');
Route::rule('swoole/addTask','admin/Client/addTask');
Route::rule('client/start','admin/login/start');
Route::rule('client/send','admin/Client/send');
Route::rule('client/shutdown','admin/Client/shutdown');
Route::rule('client/onReceive','admin/Client/onReceive');
/**
 * miss路由
 * 没有定义的路由全部使用该路由
 */
//Route::miss('admin/Login/login');
return [
];
