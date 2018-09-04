<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
/**
 * 数据签名认证
 * @param  array  $data 被认证的数据
 * @return string       签名
 */
if (!function_exists('sign')) {
    function sign($data)
    {
        // 数据类型检测
        if (! is_array($data)) {
            $data = (array)$data;
        }
        ksort($data); // 排序
        $code = http_build_query($data); // url编码并生成query字符串
        $sign = sha1($code); // 生成签名
        return $sign;
    }
}

/**打印输出
 * @param $arr
 * @author 原点 <467490186@qq.com>
 */
if (!function_exists('p')) {
    function p($arr)
    {
        echo '<pre>' . print_r($arr, true) . '</pre>';
    }
}

/**
 * 将返回的数据集转换成树
 * @param  array   $list  数据集
 * @param  string  $pk    主键
 * @param  string  $pid   父节点名称
 * @param  string  $child 子节点名称
 * @param  integer $root  根节点ID
 * @return array          转换后的树
 */
if (!function_exists('list_to_tree')) {
    function list_to_tree($list, $pk='id', $pid = 'pid', $child = 'child', $root = 0) {
        // 创建Tree
        $tree = array();
        if(is_array($list)) {
            // 创建基于主键的数组引用
            $refer = array();
            foreach ($list as $key => $data) {
                $refer[$data[$pk]] =& $list[$key];
            }
            foreach ($list as $key => $data) {
                // 判断是否存在parent
                $parentId =  $data[$pid];
                if ($root == $parentId) {
                    $tree[] =& $list[$key];
                }else{
                    if (isset($refer[$parentId])) {
                        $parent =& $refer[$parentId];
                        $parent[$child][] =& $list[$key];
                    }
                }
            }
        }
        return $tree;
    }
}

/**
 * 获取当前登陆用户uid
 * @return mixed
 * @author 原点 <467490186@qq.com>
 */
if (!function_exists('get_user_id')) {
    function get_user_id(){
        return session('user_auth.uid');
    }
}
function http_curl($url, $data =[],$header=[],$ispost=true){

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

/**
 * @param string $msg 待提示的消息
 * @param int $time   弹出维持时间（单位秒）
 * @author 原点 <467490186@qq.com>
 */
function alert_error($msg='',$time=3){
    if(request()->isAjax()){
        $str = [
            'code' => 0,
            'msg'  => $msg,
            'wait' => $time,
        ];
        $response = think\Response::create($str, 'json');
    }else{
        $str='<script type="text/javascript" src="/layui/layui.js"></script>';
        $str.='<script>
        layui.use([\'layer\'],function(){
           layer.msg("'.$msg.'",{icon:"5",time:'.($time*1000).'},function() {
              parent.location.reload();
           });
	    })
    </script>';
        $response = think\Response::create($str, 'html');
    }

    throw new think\exception\HttpResponseException($response);
}

