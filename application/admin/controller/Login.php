<?php
/**
 * Created by originThink
 * Author: 原点 467490186@qq.com
 * Date: 2018/1/16
 * Time: 15:24
 */

namespace app\admin\controller;

use think\Controller;
use app\admin\service\UserService;
use app\admin\model\User as UserModel;
use think\captcha\Captcha;
use think\facade\Config;

class Login extends Controller
{
    /**
     * 用户登录
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author 原点 <467490186@qq.com>
     */
    public function login()
    {
        if (get_user_id()) {
            $this->redirect(url('/admin/index'));
        } else {
            if (!request()->isPost()) {
                $is_open_captcha = config('captcha.is_open');
                $this->assign('is_open_captcha', $is_open_captcha);
                return $this->fetch();
            } else {
                $data = input();
                $result = UserService::login($data);
                return $result;
            }
        }

    }

    /**
     * 用户退出
     * @return array
     * @author 原点 <467490186@qq.com>
     */
    public function logout()
    {
        session('user_auth', null);
        session('user_auth_sign', null);
        return ['msg' => '退出成功', 'url' => url('/admin/login')];
    }

    /**
     * 解锁
     */
    public function unlock()
    {
        if (!$this->request->isPost()) {
            $this->error('非法请求');
        }
        $uid = get_user_id();
        if (!$uid) {
            $this->error('登录信息过期', url('/admin/login'));
        }
        $password = input('password', '', 'trim');

        $psd = UserModel::where('uid', '=', get_user_id())->value('password');
        if (password_verify($password, $psd)) {
            $this->success('解锁成功');
        } else {
            $this->error('密码错误');
        }
    }

    /**
     * 获取验证码
     * @return mixed
     */
    public function verify()
    {
        $config = Config::get('captcha.');
        $captcha = new Captcha($config);
        return $captcha->entry();
    }
}