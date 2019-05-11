<?php
/**
 * Created by originThink
 * Author: 原点 467490186@qq.com
 * Date: 2019/1/3
 * Time: 16:03
 */

namespace check\middleware;

use check\exception\ApiException;
use think\facade\Cache;
use auth\Aes;

class Check
{
    private $config = [
        'apptypes' => ['ios', 'android'],  // APP类型
        'app_sign_time' => 90,             // sign失效时间
        'app_sign_cache_time' => 20,       // sign 缓存失效时间
    ];
    public function __construct()
    {
        $this->config = array_merge($this->config,  config('api.'));
    }

    public function handle($request, \Closure $next)
    {
       ;
        $headers = request()->header();
        // 验证sign是否存在
        if (!isset($headers['sign']) || empty($headers['sign'])) {
            throw new ApiException('sign不存在', 400);
        }
        //验证app_type类型是否合法
        if (!in_array($headers['app_type'], $this->config['apptypes'])) {
            throw new ApiException('app_type不合法', 400);
        }
        //验证sign是否正确
        if (!$this->checkSignPass($headers)) {
            throw new ApiException('授权码sign失败', 401);
        }
        //添加缓存，用于唯一性校验
        Cache::set($headers['sign'], 1, $this->config['app_sign_cache_time']);

        $request->headers = $headers;

        return $next($request);
    }

    /**
     * sign 加密方式，设备唯一id（did），设备类型（app_type），请求时间（time）
     * 拼接方式，参数1=值1&参数2=值2，示例did=xx&app_type=3&time=1546571987153(毫秒时间戳)
     * @param $data
     * @return bool
     */
    public  function checkSignPass($data) {
        //解密签名
        $str = (new Aes())->decrypt($data['sign']);

        if(empty($str)) {
            return false;
        }
        //解析加密参数
        parse_str($str, $arr);
        //判断加密参数是否合法
        if (!is_array($arr) || empty($arr['did']) || $arr['did'] != $data['did']) {
            return false;
        }
        //判断是否开启debug模式
        if(!config('app_debug')) {
            //验证签名是否过期
            if ((time() - ceil($arr['time'] / 1000)) > $this->config['app_sign_time']) {
                return false;
            }
            // 唯一性判定
            if (Cache::has($data['sign'])) {
                return false;
            }
        }
        return true;
    }
}