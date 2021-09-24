<?php
/**
 * Created by originThink
 * Author: 原点 467490186@qq.com
 * Date: 2017/5/9
 * Time: 13:54
 */

namespace app\controller;

use app\model\Config;
use app\BaseController;
use think\facade\Env;
use think\facade\View;

class Common extends BaseController
{
    public $uid;             //用户id
    public $group_id;       //用户组

    /**
     * 后台控制器初始化
     */
    protected function initialize()
    {
        $this->uid = $this->request->LoginUid;
        $this->group_id = $this->request->LoginGroupId;
        $this->config();
        $site_config = $this->siteConfig();
        $this->assign('site_config', $site_config);
    }

    /**
     * 动态配置
     * @author 原点 <467490186@qq.com>
     */
    private function config()
    {
        if (cache('config')) {
            $list = cache('config');
        } else {
            $list = Config::where('name', '=', 'system_config')->field('value,status')->find();
            cache('config', $list);
        }
        if ($list['status'] == 1) {
            if (isset($list['value']['debug']) && $list['value']['debug']) {
                Env::set('app_debug', $list['value']['debug']);
            }
            if (isset($list['value']['trace']) && $list['value']['trace']) {
                Config::set(['type' => $list['value']['trace']], 'trace');
            }
        }
    }

    /**
     * 站点配置信息
     * @return array|mixed|\PDOStatement|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function siteConfig()
    {
        $site_config = cache('site_config');
        if ($site_config) {
            return $site_config;
        }
        $list = Config::where('name', '=', 'site_config')->field('value')->find();
        cache('site_config', $list);
        return $list;
    }

    /**
     * 模板变量赋值
     * @param $name
     * @param null $value
     * @date 2021/9/13 10:56
     * @author 原点 467490186@qq.com
     */
    public function assign($name, $value = null)
    {
        View::assign($name, $value);
    }

    /**
     * 解析和获取模板内容 用于输出
     * @param string $template
     * @param array $vars
     * @return string
     * @date 2021/9/13 10:56
     * @author 原点 467490186@qq.com
     */
    public function fetch(string $template = '', array $vars = [])
    {
        return View::fetch($template, $vars);
    }
}