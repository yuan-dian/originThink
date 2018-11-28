<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2018/9/7
 * Time: 10:00
 */

namespace app\admin\service;

use app\admin\model\User;
use app\admin\model\LoginLog;
use app\admin\model\AuthGroupAccess;
use think\facade\Request;
use app\admin\traits\Result;
class UserService
{
    use Result;
    /**
     * 验证登录
     * @param $data  待验证数据
     * @return array|string
     * @author 原点 <467490186@qq.com>
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function login($data)
    {
        $validate = validate('User');
        if ( !$validate->check($data) ){
            return Result::error($validate->getError());
        }
        $list = User::where(['user'=>$data['user']])->find();
        if ( empty($list) ){
            return reResult::error('账号不存在');
        }
        if ( $list['status']==0 ){
            $msg = Result::error('账号禁用');
        } elseif (!password_verify($data['password'],$list['password'])){
            $msg=Result::error('密码错误');
        } else {
            self::autoSession($list['uid']);
            $msg=Result::success('登录成功',url('/admin/index'));
        }
        return $msg;
    }

    /**
     * 记录session
     * @param $uid 用户id
     * @author 原点 <467490186@qq.com>
     * @throws \think\Exception\DbException
     */
    private static function autoSession($uid)
    {
        /* 更新登录信息 */
        $data = [
            'uid'             => $uid,
            'login_count'     => ['inc', 1],
            'last_login_ip'   => request()->ip(),
            'last_login_time' => time(),
        ];
        //更新记录
        User::update($data);
        //获取用户组
        $group_id = model('AuthGroupAccess')->where('uid','=',$uid)->column('group_id');
        //获取用户信息
        $user = User::get($uid);
        /* 记录登录SESSION */
        $auth = [
            'uid'               => $user['uid'],
            'user'              => $user['user'],
            'name'              => $user['name'],
            'head'              => $user['head'],
            'group_id'          => $group_id,
            'updatapassword'    => $user['updatapassword'],
            'last_login_time'   => $user['last_login_time'],
            'login_count'       => $user['login_count'],
            'last_login_ip'     => $user['last_login_ip'],
        ];
        //设置session
        session('user_auth', $auth);
        //设置session签名
        session('user_auth_sign', sign($auth));
        self::log($user);
    }

    /**
     * 记录登录日志
     * @param $data
     * @author 原点 <467490186@qq.com>
     */
    private static function log($data){
        //添加数据
        $LoginLog = new LoginLog;
        $LoginLog->save( [
            'uid'=>$data['uid'],
            'user'=>$data['user'],
            'name'=>$data['name'],
            'last_login_ip'=>$data['last_login_ip'],
        ]);
    }

    /**
     * 修改密码
     * @param $uid     用户id
     * @param $oldpsd  原密码
     * @param $newpsd  新密码
     * @return array
     * @author 原点 <467490186@qq.com>
     * @throws \think\Exception\DbException
     */
    public static function editPassword($uid,$oldpsd,$newpsd)
    {
        $list = User::get($uid);
        if ( !password_verify($oldpsd,$list['password']) ){
            $msg = Result::error('原密码错误');
            return $msg;
        }
        $list->password = password_hash($newpsd, PASSWORD_DEFAULT);
        $list->updatapassword=1;
        if ( $list->save() ){
            //清除当前登录信息
            session('user_auth', null);
            session('user_auth_sign', null);
            $msg = Result::success('重置成功,请重新登录',url('/admin/login'));
        }else{
            $msg = Result::error('修改失败');
        }
        return $msg;
    }

    /**
     * 添加用户
     * @param $data
     * @return array
     * @author 原点 <467490186@qq.com>
     * @throws \Exception
     */
    public static function add($data)
    {
        //验证数据合法性
        $validate = validate('User');
        if (!$validate->scene('add')->check($data)){
            //令牌数据无效时重置令牌
            $validate->getError()!='令牌数据无效'? $token=Request::token():$token='';
            $msg = Result::error($validate->getError(), null, ['token' =>$token]);
            return $msg;
        }
        $user           = new User;
        $user->user     = $data['user'];
        $user->name     = $data['name'];
        $user->status   = $data['status'];
        $user->password = password_hash($data['password'], PASSWORD_DEFAULT);
        $res = $user->save();
        if ( $res ){
            $group_ids = explode(',',$data['group_id']);
            $save=[];
            foreach ($group_ids as $v){
                $save[] = [
                    'uid'=>$user->uid,
                    'group_id'=>$v
                ];
            }
            $AuthGroupAccess = new AuthGroupAccess;
            $res2 = $AuthGroupAccess->saveAll($save,false);
            if ( $res2 ){
                $msg = Result::success('添加成功',url('/admin/userList'));
            }else {
                $msg = Result::error('添加失败', null, ['token' => Request::token()]);
            }
        }
        return $msg;
    }

    /**
     * 编辑用户
     * @param $data
     * @return array|string
     * @author 原点 <467490186@qq.com>
     * @throws \Exception
     */
    public static function edit($data)
    {
        $userdata=[
            'name'   => $data['name'],
            'status' => $data['status'],
        ];
        $res = User::update($userdata,['uid'=>$data['uid']]);
        if ( $res ) {
            AuthGroupAccess::where('uid','=',$data['uid'])->delete();
            $group_ids = explode(',',$data['group_id']);
            $save = [];
            foreach ($group_ids as $v){
                $save[] = [
                    'uid'      => $data['uid'],
                    'group_id' => $v
                ];
            }
            $AuthGroupAccess = new AuthGroupAccess;
            $res2=$AuthGroupAccess->saveAll($save,false);
            if ($res2){
                $msg = Result::success('编辑成功',url('/admin/userlist'));
            } else {
                $msg = Result::error('编辑失败');
            }
        } else {
            $msg = Result::error('编辑失败');
        }
        return $msg;
    }

    /**
     * 删除用户
     * @param $uid 用户id
     * @return array|string
     * @author 原点 <467490186@qq.com>
     * @throws \Exception
     */
    public static function delete($uid)
    {
        if ( !$uid ){
            return Result::error('参数错误');
        }
        if( $uid == 1 ){
            return Result::error('超级管理员无法删除');
        }
        $res = User::destroy($uid);
        if ( $res ){
            AuthGroupAccess::where('uid','=',$uid)->delete();
            $msg = Result::success('删除成功');
        } else {
            $msg = Result::error('删除失败');
        }
        return $msg;
    }

}