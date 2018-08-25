<?php
/**
 * Created by PhpStorm.
 * User: zhuangjun
 * Date: 2018/8/25
 * Time: 14:13
 */

namespace http;

use think\swoole\template\Timer;
class Time2 extends Timer
{
    public function _initialize(...$arg)
    {
        // TODO: Implement _initialize() method.
    }
    public function run()
    {
        $i=0;
        // TODO: Implement run() method.
        while($i<10){
            var_dump('Time2:'.$i);
            $i++;
//            sleep(1);
        }
    }
}