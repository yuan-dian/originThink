<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\facade\Env;

// +----------------------------------------------------------------------
// | Swoole设置 php think swoole命令行下有效
// +----------------------------------------------------------------------
return [
    // 扩展自身配置
    'host'         => '0.0.0.0', // 监听地址
    'port'         => 9601, // 监听端口
    'type'         => 'server', // 服务类型 支持 socket http server
    'mode'         => SWOOLE_PROCESS,
//    'socket_type'  => SWOOLE_SOCK_TCP,
    'sockType'  => SWOOLE_SOCK_TCP,
    'swoole_class' => '', // 自定义服务类名称

    // 可以支持swoole的所有配置参数
    'daemonize'    => false,
    'pid_file'     => Env::get('runtime_path') . 'swoole_server.pid',
    'log_file'     => Env::get('runtime_path') . 'swoole_server.log',
//    'task_worker_num'=>4,
    // 事件回调定义
    'onReceive'   => function($server, $fd,  $reactor_id,  $fromdata){
       new http\Receive($server, $fd,  $reactor_id,  $fromdata);
    },
//    'onTask'=>function($server, $task_id,$from_id, $data){
//        return (new http\Task())->send($data);
//    },
//    'onFinish'=>function($server,$task_id,$data){

//    },
    'onClose'      => function ($ser, $fd) {
        echo "client {$fd} closed\n";
    },
];
