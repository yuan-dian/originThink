<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/19
 * Time: 10:08
 */

namespace app\admin\behavior;
use think\Controller;
class AuthCheck extends Controller
{
    protected $uid;
    protected $group_id;
    protected $config = [
        'not_auth_tip' => '无权限操作!',  //无权限的提示信息
        'is_cache' => true,                //是否将规则缓存
        'expire' => 0,                      //缓存时间
        'prefix' => '',                     //缓存前缀
        'name' => 'user_auth',             //缓存key
        'exclude_rule' => []               //不验证权限的url
    ];
    public function __construct()
    {
        $config=config('auth.');//获取权限配置
        $this->config = array_merge($this->config, $config);
    }
    public function run()
    {
        $this->checkLogin();//验证是否登录
        $this->check();     //验证权限
        $this->getMenu();   //获取菜单
    }

    /**
     * 验证是否登录
     */
    private function checkLogin()
    {
        $user = session('user_auth');
        //验证是否登录
        if (!$user || session('user_auth_sign') != sign($user)){
            return   $this->error('登录后查看', url('/admin/login'));
        }
        //验证是否需要重置密码
        if($user['updatapassword']==0){
            return $this->error('重置密码后使用',url('/admin/resetpassword') );
        }
        $this->uid=$user['uid'];
        $this->group_id=$user['group_id'];
    }

    /**
     * 验证权限
     */
    private function check(){
        if($this->uid!=1){
            $Auth=new \org\Auth();
            $exclude_rule=$this->config['exclude_rule'];//获取免权限验证的规则
            $path=request()->path();//获取当前访问的url
            //当免权限验证规则为 * 时不进行验证
            if(!in_array($path,$exclude_rule) && !in_array('*',$exclude_rule)){//判断是否是免权限验证的规则
                if(!$Auth->check(request()->path(),$this->uid)) { //验证当前访问页面的权限
                    $this->error($this->config['not_auth_tip']);
                }
            }
        }
    }

    /**
     * 获取菜单
     */
    private function getMenu(){
        if($this->config['is_cache']){//判断是否开启缓存
            if($this->uid!=1){
                $key=$this->config['prefix'].$this->config['name'].$this->group_id;
            }else{
                $key=$this->config['prefix'].$this->config['name'].'super';
            }
            $ruleslist= cache($key);//获取对应用户缓存菜单列表
            if(!$ruleslist){
                $this->getMenuData();
            }
        }else{
            $this->getMenuData();
        }
    }

    /**
     * 获取菜单数据
     */
    private function getMenuData(){
        if($this->uid!=1){
            $rules_id=db('AuthGroup')->where(['id'=>$this->group_id])->value('rules');
            $ruleslist=db('auth_rule')->where([['id','in',$rules_id],['menu','=',1]])->order('sort desc')->select();
            $ruleslist=list_to_tree($ruleslist);
            $key=$this->config['prefix'].$this->config['name'].$this->group_id;
        }else{
            $ruleslist=db('auth_rule')->where(['menu'=>1])->order('sort desc')->select();
            $ruleslist=list_to_tree($ruleslist);
            $key=$this->config['prefix'].$this->config['name'].'super';
        }
        cache($key, $ruleslist ,$this->config['expire'],'ruleslist');//将用户对应的菜单写入缓存
    }
}