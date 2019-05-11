<?php
/**
 * Created by originThink
 * Author: 原点 467490186@qq.com
 * Date: 2018/9/7
 * Time: 17:27
 */

namespace app\admin\traits;


use think\facade\Request;

trait Result
{
    /**
     * @param string $msg
     * @param null $url
     * @param string $data
     * @param int $wait
     * @return array|string
     */
    public static function success($msg = '', $url = null, $data = '', $wait = 3)
    {
        $msg = [
            'code' => 1,
            'msg' => $msg,
            'url' => $url,
            'data' => $data,
            'wait' => $wait,
        ];
        return $msg;
    }

    /**
     * @param string $msg
     * @param null $url
     * @param string $data
     * @param int $wait
     * @return array|string
     */
    public static function error($msg = '', $url = null, $data = '', $wait = 3)
    {
        if (is_null($url)) {
            $url = Request::isAjax() ? '' : 'javascript:history.back(-1);';
        }
        $msg = [
            'code' => 0,
            'msg' => $msg,
            'url' => $url,
            'data' => $data,
            'wait' => $wait,
        ];
        return $msg;
    }
}