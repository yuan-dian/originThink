<?php
namespace app\admin\controller;
use client\Client;
use think\facade\Request;
use think\Template;

class Index extends Common
{
    /**
     * 首页
     * @return mixed
     * @author 原点 <467490186@qq.com>
     */
    public function index()
    {
        return $this->return_fetch();
    }

    /**
     * layui 首页
     * @return mixed
     * @author 原点 <467490186@qq.com>
     */
    public function home()
    {
        return $this->return_fetch();
    }
}
