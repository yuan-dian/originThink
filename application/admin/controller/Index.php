<?php
namespace app\admin\controller;
use client\Client;
use think\facade\Request;
use think\Template;

class Index extends Common
{
    protected $config=[];
    /**
     * 首页
     * @return mixed
     * @author 原点 <467490186@qq.com>
     */
    public function index()
    {
        //获取权限配置
        $this->config=config('auth.');
        //获取菜单
        $ruleslist=$this->getMenu();
        $this->assign('ruleslist',$ruleslist);
        return $this->fetch();
    }

    /**
     * 获取菜单
     */
    private function getMenu(){
        if($this->config['is_cache']){//判断是否开启缓存
            if($this->uid!=1){
                $key=$this->config['prefix'].'menu_'.implode('-',$this->group_id);
            }else{
                $key=$this->config['prefix'].'menu_'.'super';
            }
            $ruleslist= cache($key);//获取对应用户缓存菜单列表
            if(!$ruleslist){
                $ruleslist=$this->getMenuData();
            }
        }else{
            $ruleslist=$this->getMenuData();
        }
        return $ruleslist;
    }

    /**
     * 获取菜单数据
     */
    private function getMenuData(){
        if($this->uid!=1){
            $rules_id=db('AuthGroup')->where('id','in',$this->group_id)->column('rules');
            $ids=[];
            foreach ($rules_id as $g) {
                $ids = array_merge($ids, explode(',', trim($g, ',')));
            }
            $ids = array_unique($ids);
            $ruleslist=db('auth_rule')->where([['id','in',$ids],['menu','=',1]])->order('sort desc')->select();
            $ruleslist=list_to_tree($ruleslist);
            $key=$this->config['prefix'].'menu_'.implode('-',$this->group_id);
        }else{
            $ruleslist=db('auth_rule')->where(['menu'=>1])->order('sort desc')->select();
            $ruleslist=list_to_tree($ruleslist);
            $key=$this->config['prefix'].'menu_'.'super';
        }
        cache($key, $ruleslist ,$this->config['expire'],$this->config['cache_tag']);//将用户对应的菜单写入缓存
        return $ruleslist;
    }

    /**
     * layui 首页
     * @return mixed
     * @author 原点 <467490186@qq.com>
     */
    public function home()
    {
        return $this->fetch();
    }
}
