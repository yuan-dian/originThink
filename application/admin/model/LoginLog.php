<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2018/9/7
 * Time: 16:47
 */

namespace app\admin\model;

use think\Model;
class LoginLog extends Model
{
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;

    public function searchNameAttr($query, $value)
    {
        if($value){
            $query->where('user|name','like', '%' .$value . '%');
        }
    }

    public function searchCreateTimeAttr($query, $value, $data)
    {
        if( $value[0] && $value[1] ){
            $query->whereBetweenTime('create_time', $value[0], $value[1]);
        }
    }
}