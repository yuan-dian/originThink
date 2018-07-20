<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/27
 * Time: 15:13
 */

namespace app\admin\controller;
use swoole_server;

class Swoole
{
    private $serv;
    public function __construct() {
        $this->serv = new swoole_server("0.0.0.0", 9601);
        $this->serv->set(array(
            'worker_num' => 1,
//            'log_file' => '/mnt/logs/swoole/swoole.log',
//            'pid_file' => '/mnt/logs/swoole/swoole.pid',
            'max_request' => 10000,
            'task_worker_num' => 1,
//            'user' => 'www-data',
//            'group' => 'www-data'
        ));
        $this->serv->on('WorkerStart', array($this, 'onWorkerStart'));
        $this->serv->on('Receive', array($this, 'onReceive'));
        $this->serv->on('Task', array($this, 'onTask'));
        $this->serv->on("Finish", array($this, "onFinish"));
    }

    public function onReceive($server, $fd, $from_id, $fromdata)
    {
        dump($fd);
//        $list=db()->query('select sleep(3)');
//        $this->serv->send($fd, json_encode($list));
//
        include 'receive.php';
    }
    public function onTask($serv,$task_id,$from_id, $data) {
        switch ($data['type']){
            case 'sql'://并发执行SQL并返回执行结果
                $list=db()->query($data['sql']);
                return $list;
                break;
            case 'async'://异步执行curl
                $url=$data['data']['url'];

                if(isset($data['data']['param'])){
                    $param=$data['data']['param'];
                }else{
                    $param=[];
                }
                if(isset($data['data']['header'])){
                    $header=$data['data']['header'];
                }else{
                    $header=[];
                }
                if(isset($data['data']['ispost'])){
                    $ispost=$data['data']['ispost'];
                }else{
                    $ispost=true;
                }
                $list=$this->http_curl($url,$param,$header,$ispost);
                return $list;
                break;
        }
//        include 'task.php';
    }
    public function onFinish( $serv, int $task_id, string $data){
//        echo "onFinish\n";
    }
    public function onStart( $serv){
//        echo "onStart\n";
    }
    public function onWorkerStart( $serv , $worker_id) {
        echo "onWorkerStart\n";
    }
    public function start()
    {
        $this->serv->start();
    }

    public function http_curl($url, $data =[],$header=[],$ispost=true){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //判断是否加header
        if($header){
            curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
        }
        //判断是否是POST请求
        if($ispost){
            // post数据
            curl_setopt($ch, CURLOPT_POST, 1);
            // post的变量
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        $output = curl_exec($ch);
        curl_close($ch);
        //打印获得的数据
        return $output;
    }

}