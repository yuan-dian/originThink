<?php
/**
 * Created by PhpStorm.
 * User: zhuangjun
 * Date: 2018/8/20
 * Time: 17:20
 */

namespace http;

class Task
{
    /**
     * 获取数据执行类型
     * @param array $data
     */
    public function send(array $data)
    {
        $function=$data['type'];
        if(method_exists ($this,$function)){
            $list=$this->$function($data['sql']);
            var_dump($list);
           return $list;
        } else{
            return false;
        }
    }

    /**
     * 异步执行SQL
     * @param string $sql  sql 语句
     * @return mixed
     */
    protected function sql(string $sql)
    {
        $list=db()->query($sql);
        return $list;
    }

    /**
     * 异步执行curl
     * @param array $data
     */
    protected function async(array $data)
    {
        $this->http_curl($data);
    }

    /**
     * CURL请求
     * @param $data
     * @return bool|mixed
     */
    protected function http_curl($data){
        if(!isset($data['url']) || !$data['url']){
            return false;
        }
        $url=isset($data['url'])?$data['url']:'';
        $data=isset($data['data'])?$data['data']:[];
        $header=isset($data['header'])?$data['header']:[];
        $ispost=isset($data['ispost'])?$data['ispost']:true;
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