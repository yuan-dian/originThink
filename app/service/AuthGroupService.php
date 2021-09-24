<?php
/**
 * Created by originThink
 * Author: 原点 467490186@qq.com
 * Date: 2018/9/9
 * Time: 9:10
 */

namespace app\service;

use app\traits\Result;
use app\model\AuthGroup;
use think\facade\Cache;
use think\Model;

class AuthGroupService
{
    use Result;

    /**
     * 添加用户组
     * @param $title 用户组名称
     * @return array|string
     * @author 原点 <467490186@qq.com>
     */
    public static function add($title)
    {
        $group = new AuthGroup;
        $group->title = $title;
        $res = $group->save();
        if ($res) {
            $mag = Result::success('添加成功');
        } else {
            $mag = Result::error('添加失败');
        }
        return $mag;
    }

    /**
     * @param $id   用户组id
     * @param $data 修改数据
     * @param bool $authcache 是否删除缓存
     * @author 原点 <467490186@qq.com>
     * @return array|string
     */
    public static function edit($id, $data, $authcache = false)
    {
        if (!$id || !$data) Result::error('参数错误');
        $res = AuthGroup::where('id', '=', $id)->save($data);
        if ($res) {
            if ($authcache) {
                Cache::clear(config('auth.cache_tag'));//清除Auth类设置的缓存
            }
            $mag = Result::success('编辑成功');
        } else {
            $mag = Result::error('编辑失败');
        }
        return $mag;
    }
}