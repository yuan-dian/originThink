<?php
/**
 * Created by PhpStorm.
 * User: zhuangjun
 * Date: 2018/8/20
 * Time: 16:51
 */

namespace http;


class Receive
{
    protected $serv;
    protected $fd;
    public function __construct($serv, $fd,  $reactor_id,  $fromdata)
    {
        $this->serv=$serv;
        $this->fd=$fd;
        $data=json_decode($fromdata,true);
        if($data && is_array($data)){
            $this->getType($data);
        }else{
            $this->serv->send($this->fd, json_encode(['code'=>0,'msg'=>'参数错误']));
        }
    }

    /**
     * 获取数据执行类型
     * @param array $data
     */
    public function getType(array $data)
    {
//        var_dump($data);
        $function=$data['type'];
        if(method_exists ($this,$function)){
            $this->$function($data['data']);
        } else{
            $this->serv->send($this->fd, json_encode(['code'=>0,'msg'=>'参数错误']));
        }
    }

    /**
     * 同时执行多个SQL
     * @param array $data
     */
    public function sql(array $data)
    {
        $tasks=[];
        foreach ($data as $v){
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
        $this->serv->send($this->fd, json_encode($res));
    }

    /**
     * 异步请求接口
     * @param $data
     */
    public function async($data)
    {
        if(!$data['url']){
            $res=['code'=>0, 'msg'=>'参数错误',];
        }else{
            $send=['type'=>'async','data'=>$data];//发送数据
            $task_id=$this->serv->task($send);//投递任务
            if($task_id!==false){
                $res=['code'=>1, 'msg'=>'投递成功',];
            }else{
                $res=['code'=>0, 'msg'=>'投递失败',];
            }
        }
        $this->serv->send($this->fd, json_encode($res));
    }
    

}