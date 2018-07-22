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

    public function getMenu()
    {
        if($this->uid!=1){
            $ruleslist= cache('ruleslist_'.$this->group_id );//获取当前用户组菜单
        }else{
            $ruleslist= cache('ruleslist_admin' );//获取当前用户组菜单
        }
        return json_encode($ruleslist);
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
