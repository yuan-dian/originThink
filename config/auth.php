<?php
/**
 * Created by PhpStorm.
 * User: zhuangjun
 * Date: 2018/7/16
 * Time: 10:45
 */
return [
    'not_auth_tip' => '无权限操作!',  //无权限的提示信息
    'is_cache' => true,                //是否将规则缓存
    'expire' => 3600,                   //缓存时间
    'prefix' => '',                     //缓存前缀
    'name' => 'user_auth',             //缓存key
    'exclude_rule' => [
        'admin/index'
    ]               //不验证权限的url
];