<?php
/**
 * Created by originThink
 * Author: 原点 467490186@qq.com
 * Date: 2019/1/3
 * Time: 17:12
 */

namespace check;

class Aes
{
    private $config = [
        'key' => '863b42d64d68e913',
        'cipher' => 'AES-256-CBC',
        'iv' => '9410b305637c69eb'
    ];

    /**
     *
     * @param $key        密钥
     * @return String
     */
    public function __construct()
    {
        $this->config = array_merge($this->config,  config('api.'));
    }

    /**
     * 加密
     * @param String data加密的字符串
     * @param String key   解密的key
     * @return HexString
     */
    public function encrypt($data = '')
    {
        $data = json_encode($data);
        if (in_array($this->config['cipher'], openssl_get_cipher_methods()))
        {
            $data = openssl_encrypt($data, $this->config['cipher'], $this->config['key'], 0, $this->config['iv'] );
            return $data;
        }
        return false;
}

    /**
     * 解密
     * @param String input 解密的字符串
     * @return String
     */
    public function decrypt($sStr)
    {
        if (in_array($this->config['cipher'], openssl_get_cipher_methods()))
        {
            $data = openssl_decrypt($sStr, $this->config['cipher'], $this->config['key'], 0, $this->config['iv'] );
            return  json_decode($data, true);
        }
        return false;
    }
}