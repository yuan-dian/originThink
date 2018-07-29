<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/9
 * Time: 13:54
 */

namespace app\admin\controller;
use think\Controller;
use think\Loader;
class Common extends Controller
{
    public $uid;             //用户id
    public $group_id;       //用户组
    /**
     * 后台控制器初始化
     */
    protected function initialize()
    {
        $user = session('user_auth');
        $this->uid=$user['uid'];
        $this->group_id=$user['group_id'];
        $this->config();
    }

    /**
     * 动态配置
     * @author 原点 <467490186@qq.com>
     */
    private function config(){
        config('template.view_path','layui');
        if(cache('config')){
            $list=cache('config');
        }else{
            $list=db('config')->where('name','=','system_config')->json(['value'])->field('value,status')->find();
            cache('config',$list);
        }
        if($list['status']==1){
            config('app_debug',$list['value']['debug']);
            config('app_trace',$list['value']['trace']);
            config('trace.type',$list['value']['trace_type']==0?'Html':'Console');
        }
    }

}