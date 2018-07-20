<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/27
 * Time: 15:18
 */

namespace app\admin\controller;
use swoole_client;

class Client
{
    private $client;
    public function __construct(){
        $this->client = new swoole_client(SWOOLE_SOCK_TCP);
        if (!$this->client->connect('127.0.0.1', 9601, -1))
        {
            echo ("connect failed. Error: {$this->client->errCode}\n");
        }
    }

    /**
     * 创建连接
     * @author 原点 <467490186@qq.com>
     */
    public function onConnect()
    {
        if (!$this->client->connect('127.0.0.1', 9601, -1))
        {
            echo ("connect failed. Error: {$this->client->errCode}\n");
        }
    }

    /**
     * 发送数据
     * @param $data 发送的数据
     * @return mixed
     * @author 原点 <467490186@qq.com>
     */
    public function send($data)
    {
        return $this->client->send($data);
    }

    /**
     * 接收服务器返回数据
     * @return mixed
     * @author 原点 <467490186@qq.com>
     */
    public function recv()
    {
        return $this->client->recv();
    }

    /**
     * 发送文件
     * @param $filename   指定要发送文件的路径
     * @param int $offset 上传文件的偏移量，可以指定从文件的中间部分开始传输数据。此特性可用于支持断点续传。
     * @param int $length 发送数据的尺寸，默认为整个文件的尺寸
     * @return mixed
     * @author 原点 <467490186@qq.com>
     */
    public function sendfile($filename,$offset=0,$length =0)
    {
        return $this->client->sendfile($filename,$offset,$length);
    }

    /**
     * 关闭服务
     * @param string $user 账户
     * @param string $password 密码
     * @return mixed
     * @author 原点 <467490186@qq.com>
     */
    public function shutdown()
    {
        return $this->client->send(json_encode(123));
    }
    
}