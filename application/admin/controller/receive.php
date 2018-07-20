<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/28
 * Time: 14:24
 */
$data=json_decode($fromdata,true);
if($data && is_array($data)){
    switch ($data['type']){
        case 'sql'://并发执行SQL
            $tasks=[];
            foreach ($data['data'] as $v){
                $tasks[]=[
                    'type'=>'sql',
                    'sql'=>$v
                ];
            }
            $results = $this->serv->taskWaitMulti($tasks, 10.0);
            if($results){
                $res=[
                    'code'=>1,
                    'msg'=>'执行成功',
                    'data'=>$results
                ];
            }else{
                $res=[
                    'code'=>0,
                    'msg'=>'执行失败',
                    'data'=>[]
                ];
            }
            $this->serv->send($fd, json_encode($res));
            break;
        case 'async'://异步执行接口（无返回值）
            /**
             * 参数
             * type   类型
             * url    执行接口
             * param  请求参数
             * header 请求header头
             * ispost 是否是post请求
             */
            if(!$data['data']['url']){
                $res=['code'=>0, 'msg'=>'参数错误',];
            }else{
                $send=['type'=>$data['type'],'data'=>$data['data']];//发送数据
                $task_id=$this->serv->task($send);//投递任务
                if($task_id!==false){
                    $res=['code'=>1, 'msg'=>'投递成功',];
                }else{
                    $res=['code'=>0, 'msg'=>'投递失败',];
                }
            }
            $this->serv->send($fd, json_encode($res));
            break;
        case 'after': //延时任务
            /**
             * after_time  延迟时间（毫秒）
             */
            $after_time_ms=$data['time'];
            if($after_time_ms<=86400000){ //延时时间小于86400000
                $time_id=$this->serv->after($after_time_ms,function()use($data){
                    $send=[
                        'type'=>'async',
                        'data'=>$data['data'],
                    ];
                    $this->serv->task($send);//投递任务
                });
            }else{
                $time_id=$this->serv->tick(86400000,function ($id)use(&$after_time_ms,$data){
                    if($after_time_ms<86400000){
                        $this->serv->after($after_time_ms,function()use($id,$data){
                            $send=[
                                'type'=>'async',
                                'data'=>$data['data'],
                            ];
                            $this->serv->task($send);//投递任务
                            $this->serv->clearTimer($id);//清除定时器
                        });
                    }
                    $after_time_ms=$after_time_ms-86400000;
                });
            }
            if($time_id){
                $res=['coed'=>1,'mag'=>'延时任务设置成功'];
            }else{
                $res=['coed'=>0,'mag'=>'延时任务设置失败'];
            }
            $this->serv->send($fd, json_encode($res));
            break;
        case 'tick'://定时任务
            /**
             * tick_time  间隔时间（毫秒）
             */
            $after_time_ms=$data['time'];
            if($after_time_ms<86400000){
                $time_id=$this->serv->tick($after_time_ms,function ()use($data){
                    $send=[
                        'type'=>'async',
                        'data'=>$data['data'],
                    ];
                    $this->serv->task($send);
                });
            }else{
                $time_id=$this->serv->tick(86400000,function ($id)use(&$after_time_ms,$data){
                    $after_time_ms=$after_time_ms-86400000;
                    if($after_time_ms<=86400000 && $after_time_ms>0){
                        $this->serv->after($after_time_ms,function()use($id,$data){
                            $send=[
                                'type'=>'async',
                                'data'=>$data['data'],
                            ];
                            $this->serv->task($send);
                        });
                        $after_time_ms=$data['tick_time']+$after_time_ms;
                    }
                });
            }
            if($time_id){
                $res=['coed'=>1,'mag'=>'定时任务设置成功'];
            }else{
                $res=['coed'=>0,'mag'=>'定时任务设置失败'];
            }
            $this->serv->send($fd, json_encode($res));
            break;
        case 'concurrent':
            $tasks=[];
            foreach ($data['data'] as $v){
                $tasks[]=[
                    'type'=>'async',
                    'data'=>$v
                ];
            }
            $results = $this->serv->taskWaitMulti($tasks, 10.0);
            if($results){
                $res=[
                    'code'=>1,
                    'msg'=>'执行成功',
                    'data'=>$results
                ];
            }else{
                $res=[
                    'code'=>0,
                    'msg'=>'执行失败',
                    'data'=>[]
                ];
            }
            $this->serv->send($fd, json_encode($res));
            break;
        case 'coroutine':
            $mysql = new Swoole\Coroutine\MySQL();
            $res = $mysql->connect([
                'host' => '127.0.0.1',
                'user' => 'root',
                'password' => '$IHy^f!r5&',
                'database' => 'tp5.1',
            ]);
            if ($res == false) {
                $res=[
                    'code'=>0,
                    'msg'=>'连接数据库失败',
                    'data'=>[]
                ];
            }else{
                $list=[];
//                $mysql->setDefer();
                $list = $mysql->query('select sleep(3)', 5);
                $res=[
                    'code'=>1,
                    'msg'=>'执行成功',
                    'data'=>$list
                ];
            }
            $this->serv->send($fd, json_encode($res));
            break;
    }
}else{
    $this->serv->send($fd, json_encode(['code'=>0,'msg'=>'参数错误']));
}
