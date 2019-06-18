<?php
/**
 * Created by originThink
 * Author: 原点 467490186@qq.com
 * Date: 2018/1/16
 * Time: 15:24
 */
namespace app\admin\controller;

use auth\Auth;
use app\admin\model\User;
use app\admin\model\Config;

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
        $menuList = (new Auth($this->uid, $this->group_id))->getMenuList();
        $this->assign('menuList', $menuList);
        $info = User::get($this->uid)->hidden(['password']);
        $info['head'] ? : $info['head'] = '/images/face.jpg';
        $this->assign('info', $info);
        //公告
        $notice_config = $this->noticeConfig();
        $this->assign('notice_config', $notice_config);
        return $this->fetch();
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
