<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/16
 * Time: 15:38
 */

namespace app\admin\model;
use think\Model;

class User extends Model
{
    protected $autoWriteTimestamp = true;
    protected $pk = 'uid';
    protected $type = [
        'last_login_time'  =>  'timestamp',
    ];
    public function groupIds()
    {
        return $this->hasMany('AuthGroupAccess','uid');
    }
    /**
     * @param $data
     * @return array
     * @author 原点 <467490186@qq.com>
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login($data)
    {
        $validate =validate('User');
        if(!$validate->check($data)){
            $msg=[
                'code'=>0,
                'msg'=>$validate->getError()
            ];
        }else{
            $list=$this->where(['user'=>$data['user']])->find();
            if($list){
                if($list['status']==0){
                    $msg=[
                        'code'=>0,
                        'msg'=>'账号禁用'
                    ];
                }elseif($list['password']!=md5(md5($data['password']).$list['random'])){
                    $msg=[
                        'code'=>0,
                        'msg'=>'密码错误'
                    ];
                }else{
                    $this->autoLogin($list);
                    $msg=[
                        'code'=>1,
                        'msg'=>'登录成功',
                        'url'=>url('/admin/index')
                    ];
                }
            }else{
                $msg=[
                    'code'=>0,
                    'msg'=>'账号不存在'
                ];
            }
        }
        return $msg;
    }

    /**
     * 记录登录信息
     * @param $user
     * @author 原点 <467490186@qq.com>
     */
    private function autoLogin($user)
    {
        /* 更新登录信息 */
        $data=[
            'login_count'=>$this->raw('login_count+1'),
            'last_login_ip'=>request()->ip(),
            'last_login_time'=>time(),
        ];
        $this->isUpdate(true)->save($data,['uid'=>$user['uid']]);
        $group_id=model('AuthGroupAccess')->where('uid',$user['uid'])->column('group_id');
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
        session('user_auth', $auth);
        session('user_auth_sign', sign($auth));
        $this->log($user['uid']);
    }

    /**
     * 记录登录日志
     * @param $uid
     * @author 原点 <467490186@qq.com>
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function log($uid){
        $data=$this->where('uid',$uid)->find();
        $add=[
            'uid'=>$data['uid'],
            'user'=>$data['user'],
            'name'=>$data['name'],
            'last_login_ip'=>$data['last_login_ip'],
            'create_time'=>time(),
        ];
        db('login_log')->insert($add);
    }
}