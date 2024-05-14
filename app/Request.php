<?php

namespace app;

// 应用请求对象类
use think\facade\Config;

class Request extends \think\Request
{
    /**
     * 获取当前请求URL的pathinfo信息(不含URL后缀)
     * @access public
     * @return string
     */
    public function path()
    {
        if (is_null($this->path)) {
            $suffix   = Config::get('route.url_html_suffix');
            $pathinfo = $this->pathinfo();

            if (false === $suffix) {
                // 禁止伪静态访问
                $this->path = $pathinfo;
            } elseif ($suffix) {
                // 去除正常的URL后缀
                $this->path = preg_replace('/\.(' . ltrim($suffix, '.') . ')$/i', '', $pathinfo);
            } else {
                // 允许任何后缀访问
                $this->path = preg_replace('/\.' . $this->ext() . '$/i', '', $pathinfo);
            }
        }

        return $this->path;
    }

}
