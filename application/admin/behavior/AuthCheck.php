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
    public function run()
    {
        $user = session('user_auth');
        if (!$user || session('user_auth_sign')!=sign($user)) {
          $this->error('登录后查看',url('/admin/login'));
        }else{
            if($user['updatapassword']==0){
                $this->success('重置密码后使用',url('/admin/resetpassword') );
            }
            $this->uid=$user['uid'];
            $this->group_id=$user['group_id'];
            if($this->uid!=1){
                $Auth=new \org\Auth();
                if(!$Auth->check(request()->path(),$this->uid)){ //验证当前访问页面的权限
                    $this->error('没有权限访问该页面');
                }else{
                    $ruleslist= cache('ruleslist_'.$this->group_id );//获取对应用户缓存菜单列表
                    if(!$ruleslist){
                        $rules_id=db('AuthGroup')->where(['id'=>$this->group_id])->value('rules');
                        $ruleslist=db('auth_rule')->where([['id','in',$rules_id],['menu','=',1]])->order('sort desc')->select();
                        $ruleslist=list_to_tree($ruleslist);
                        cache('ruleslist_'.$this->group_id, $ruleslist ,3600,'ruleslist');//将用户对应的菜单写入缓存
                    }
                }
            }else{
                $ruleslist= cache('ruleslist_admin' );//获取对应用户缓存菜单列表
                if(!$ruleslist){
                    $ruleslist=db('auth_rule')->where(['menu'=>1])->order('sort desc')->select();
                    $ruleslist=list_to_tree($ruleslist);
                    cache('ruleslist_admin', $ruleslist ,3600,'ruleslist');//将用户对应的菜单写入缓存
                }
            }
        }
    }
}