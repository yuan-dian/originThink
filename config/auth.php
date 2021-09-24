<?php
/**
 * Created by PhpStorm.
 * User: zhuangjun
 * Date: 2018/7/16
 * Time: 10:45
 */
return [
    'auth_on'           => 1,                      // 权限开关
    'auth_type'         => 1,                      // 认证方式，1为实时认证；2为登录认证。
    'auth_group'        => 'auth_group',           // 用户组数据表名
    'auth_group_access' => 'auth_group_access',    // 用户-用户组关系表
    'auth_rule'         => 'auth_rule',            // 权限规则表
    'auth_user'         => 'uese',                 // 用户信息表
    'not_auth_tip'      => '无权限操作!',            //无权限的提示信息
    'is_cache'          => true,                   //是否将规则缓存
    'expire'            => 3600,                   //缓存时间
    'prefix'            => 'user_auth_',           //缓存前缀
    'cache_tag'         => 'auth',                 //缓存key
    'exclude_rule'      => [                       //不验证权限的url
                            'admin/index',         //首页
                            'admin/home',          //系统信息
                         ]
];