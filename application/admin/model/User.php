<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/16
 * Time: 15:38
 */

namespace app\admin\model;
use think\Model;

class User extends Model
{
    protected $autoWriteTimestamp = true;
    protected $pk = 'uid';
    protected $type = [
        'last_login_time'  =>  'timestamp',
    ];
    public function groupIds()
    {
        return $this->hasMany('AuthGroupAccess','uid');
    }
}