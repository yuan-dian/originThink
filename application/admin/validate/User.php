<?php
/**
 * Created by PhpStorm.
 * User: yuandian
 * Date: 2016/9/9
 * Time: 15:39
 */

namespace app\admin\validate;
use think\Validate;

class User extends Validate
{
    protected $rule = [
        'user'  =>  'require|max:25',
        'password' =>  'require|length:4,25',
        'code'=>'require|captcha'
    ];

    protected $message = [
        'user.require'  =>  '用户名不能为空',
        'user.max'  =>  '用户名最大长度25位',
        'password.require' =>  '密码不能为空',
        'password.length' =>  '密码长度4-25位',
        'code.require'=>'验证码不能为空',
        'code.captcha'=>'验证码错误'
    ];
    public function sceneAdd()
    {
        return $this->only(['user','password'])->append('user', 'token');
    }
}