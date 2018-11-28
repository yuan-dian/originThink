<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2018/9/7
 * Time: 17:27
 */

namespace app\admin\traits;


use think\facade\Request;

trait Result
{

    public static function success($msg = '', $url = null, $data = '', $wait = 3)
    {
        $msg = [
            'code' => 1,
            'msg'  => $msg,
            'url'  => $url,
            'data' => $data,
            'wait' => $wait,
        ];
        return $msg;
    }
    public static function error($msg = '', $url = null, $data = '', $wait = 3)
    {
        if ( is_null($url) ) {
            $url = Request::isAjax() ? '' : 'javascript:history.back(-1);';
        }
        $msg=[
            'code' => 0,
            'msg'  => $msg,
            'url'  => $url,
            'data' => $data,
            'wait' => $wait,
        ];
        return $msg;
    }
}