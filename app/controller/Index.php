<?php
/**
 * Created by originThink
 * Author: 原点 467490186@qq.com
 * Date: 2018/1/16
 * Time: 15:24
 */
namespace app\controller;

use app\service\AuthService;
use app\model\User;
use app\model\Config;
use think\facade\View;

class Index extends Common
{
    /**
     * 首页
     * @return mixed
     * @author 原点 <467490186@qq.com>
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        //获取菜单
        $menuList = (new AuthService($this->uid, $this->group_id))->getMenuList();
        View::assign('menuList', $menuList);
        $info = User::find($this->uid)->hidden(['password']);
        $info['head'] ? : $info['head'] = '/images/face.jpg';
        View::assign('info', $info);
        //公告
        $notice_config = $this->noticeConfig();
        View::assign('notice_config', $notice_config);
        return View::fetch();
    }

    /**
     * layui 首页
     * @return mixed
     * @author 原点 <467490186@qq.com>
     */
    public function home()
    {
        return View::fetch();
    }

    /**
     * 公告配置信息
     * @return array|mixed|\PDOStatement|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function noticeConfig()
    {
        $notice_config = cache('notice_config');
        if ($notice_config) {
            return $notice_config;
        }
        $list = Config::where('name', '=', 'notice_config')->field('value')->find();
        cache('notice_config', $list);
        return $list;
    }
}
