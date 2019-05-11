<?php
/**
 * Created by originThink
 * Author: 原点 467490186@qq.com
 * Date: 2018/9/3
 * Time: 9:36
 */

namespace auth;

use think\Db;
use think\facade\Config;
use think\facade\Request;
use think\facade\Cache;

/**
 * 权限认证类
 * 功能特性：
 * 1，是对规则进行认证，不是对节点进行认证。用户可以把节点当作规则名称实现对节点进行认证。
 *      $auth=new Auth();  $auth->check('规则名称','用户id')
 * 2，可以同时对多条规则进行认证，并设置多条规则的关系（or或者and）
 *      $auth=new Auth();  $auth->check('规则1,规则2','用户id','and')
 *      第三个参数为and时表示，用户需要同时具有规则1和规则2的权限。 当第三个参数为or时，表示用户值需要具备其中一个条件即可。默认为or
 * 3，一个用户可以属于多个用户组(think_auth_group_access表 定义了用户所属用户组)。我们需要设置每个用户组拥有哪些规则(think_auth_group 定义了用户组权限)
 *
 * 4，支持规则表达式。
 *      在think_auth_rule 表中定义一条规则时，如果type为1， condition字段就可以定义规则表达式。 如定义{score}>5  and {score}<100  表示用户的分数在5-100之间时这条规则才会通过。
 */
//数据库
/*
-- ----------------------------
-- think_auth_rule，规则表，
-- id:主键，name：规则唯一标识, title：规则中文名称 status 状态：为1正常，为0禁用，condition：规则表达式，为空表示存在就验证，不为空表示按照条件验证
-- ----------------------------
 DROP TABLE IF EXISTS `think_auth_rule`;
CREATE TABLE `think_auth_rule` (
    `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
    `name` char(80) NOT NULL DEFAULT '',
    `title` char(20) NOT NULL DEFAULT '',
    `type` tinyint(1) NOT NULL DEFAULT '1',
    `status` tinyint(1) NOT NULL DEFAULT '1',
    `condition` char(100) NOT NULL DEFAULT '',  # 规则附件条件,满足附加条件的规则,才认为是有效的规则
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- ----------------------------
-- think_auth_group 用户组表，
-- id：主键， title:用户组中文名称， rules：用户组拥有的规则id， 多个规则","隔开，status 状态：为1正常，为0禁用
-- ----------------------------
 DROP TABLE IF EXISTS `think_auth_group`;
CREATE TABLE `think_auth_group` (
    `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
    `title` char(100) NOT NULL DEFAULT '',
    `status` tinyint(1) NOT NULL DEFAULT '1',
    `rules` char(80) NOT NULL DEFAULT '',
    PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- ----------------------------
-- think_auth_group_access 用户组明细表
-- uid:用户id，group_id：用户组id
-- ----------------------------
DROP TABLE IF EXISTS `think_auth_group_access`;
CREATE TABLE `think_auth_group_access` (
    `uid` mediumint(8) unsigned NOT NULL,
    `group_id` mediumint(8) unsigned NOT NULL,
    UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
    KEY `uid` (`uid`),
    KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
 */

class Auth
{
    /**
     * @var object 对象实例
     */
    protected static $instance;
    /**
     * 当前请求实例
     * @var Request
     */
    protected $request;
    protected $uid;
    protected $group_id;

    //默认配置
    protected $config = [
        'auth_on' => 1,                      // 权限开关
        'auth_type' => 1,                      // 认证方式，1为实时认证；2为登录认证。
        'auth_group' => 'auth_group',           // 用户组数据表名
        'auth_group_access' => 'auth_group_access',    // 用户-用户组关系表
        'auth_rule' => 'auth_rule',            // 权限规则表
        'auth_user' => 'uese',                 // 用户信息表
        'not_auth_tip' => '无权限操作!',          //无权限的提示信息
        'is_cache' => true,                   //是否将规则缓存
        'expire' => 3600,                   //缓存时间
        'prefix' => 'user_auth',            //缓存前缀
        'cache_tag' => 'auth',                 //缓存key
        'exclude_rule' => []                     //不验证权限的url
    ];

    /**
     * 类架构函数
     * Auth constructor.
     */
    /**
     * Auth constructor.
     * @param int $uid 用户id
     * @param array $group_id 用户组
     */
    public function __construct($uid = 0, $group_id = [])
    {
        //可设置配置项 auth, 此配置项为数组。
        if ($auth = Config::get('auth.')) {
            $this->config = array_merge($this->config, $auth);
        }
        // 初始化request
        $this->request = Request::instance();
        $this->uid = $uid;
        $this->group_id = $group_id;
    }

    /**
     * @param        $name     string|array  需要验证的规则列表,支持逗号分隔的权限规则或索引数组
     * @param int $type 认证类型
     * @param string $mode 执行check的模式
     * @param string $relation 如果为 'or' 表示满足任一条规则即通过验证;如果为 'and'则表示需满足所有规则才能通过验证
     * @return bool  通过验证返回true;失败返回false
     * @author 原点 <467490186@qq.com>
     */
    public function check($name, $type = 1, $mode = 'url', $relation = 'or')
    {
        //判断权限开关
        if (!$this->config['auth_on']) {
            return true;
        }
        if (in_array($name, $this->config['exclude_rule'])) {
            return true;
        }
        // 获取用户需要验证的所有有效规则列表
        $authList = $this->getAuthList($type);
        if (is_string($name)) {
            $name = strtolower($name);
            if (strpos($name, ',') !== false) {
                $name = explode(',', $name);
            } else {
                $name = [$name];
            }
        }
        $list = []; //保存验证通过的规则名
        if ('url' == $mode) {
            $REQUEST = unserialize(strtolower(serialize($this->request->param())));
        }
        foreach ($authList as $auth) {
            $query = preg_replace('/^.+\?/U', '', $auth);
            if ('url' == $mode && $query != $auth) {
                parse_str($query, $param); //解析规则中的param
                $intersect = array_intersect_assoc($REQUEST, $param);
                $auth = preg_replace('/\?.*$/U', '', $auth);
                if (in_array($auth, $name) && $intersect == $param) {
                    //如果节点相符且url参数满足
                    $list[] = $auth;
                }
            } else {
                if (in_array($auth, $name)) {
                    $list[] = $auth;
                }
            }
        }
        if ('or' == $relation && !empty($list)) {
            return true;
        }
        $diff = array_diff($name, $list);
        if ('and' == $relation && empty($diff)) {
            return true;
        }

        return false;
    }

    /**
     * 获取用户组信息,返回值为数组
     * @return array|mixed|PDOStatement|string|\think\Collection
     * @author 原点 <467490186@qq.com>
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGroups()
    {
        //获取缓存开启状态
        $is_cache = $this->config['is_cache'];
        $cache_key = '';
        //判断是否开启缓存
        if ($is_cache) {
            //设置缓存name
            $cache_key = $this->config['prefix'] . 'groups_' . implode('-', $this->group_id);
            //获取缓存
            $group = Cache::get($cache_key);
            if ($group) {
                return $group;
            }
        }
        // 执行查询
        $user_groups = \think\Db::name($this->config['auth_group'])->where('id', 'in', $this->group_id)->where('status','=',1)->select();
        if ($is_cache) {
            //设置缓存
            Cache::tag($this->config['cache_tag'])->set($cache_key, $user_groups, $this->config['expire']);
        }
        return $user_groups;
    }

    /**
     * 获得权限列表
     * @param $type 验证类型
     * @return array|mixed
     * @author 原点 <467490186@qq.com>
     */
    protected function getAuthList($type)
    {
        //获取缓存开启状态
        $is_cache = $this->config['is_cache'];
        $cache_key = '';
        //判断是否开启缓存
        if ($is_cache) {
            $t = implode(',', (array)$type);
            //设置缓存name
            $cache_key = $this->config['prefix'] . 'authList_' . $t . '_' . implode('-', $this->group_id);
            //获取缓存数据
            $_authList = Cache::get($cache_key);
            if ($_authList) {
                return $_authList;
            }
        }
        $rules = $this->_auth_rule();
        $authList = [];
        foreach ($rules as $rule) {
            $authList[] = strtolower($rule['name']);
        }
        $authList = array_unique($authList);
        if ($is_cache) {
            //设置缓存数据
            Cache::tag($this->config['cache_tag'])->set($cache_key, $authList, $this->config['expire']);
        }
        return $authList;
    }

    /**
     * 获得用户资料
     * @return array|mixed|null|PDOStatement|string|\think\Model
     * @author 原点 <467490186@qq.com>
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function getUserInfo()
    {
        //获取缓存开启状态
        $is_cache = $this->config['is_cache'];
        $cache_key = '';
        //判断是否开启缓存
        if ($is_cache) {
            //设置缓存name
            $cache_key = $this->config['prefix'] . 'user_info_' . $this->uid;
            //获取缓存
            $user_info = Cache::get($cache_key);
            if ($user_info) {
                return $user_info;
            }
        }
        $user = Db::name($this->config['auth_user']);
        // 获取用户表主键
        $_pk = is_string($user->getPk()) ? $user->getPk() : 'uid';
        $user_info = $user->where($_pk, $this->uid)->find();
        if ($is_cache) {
            //设置缓存
            Cache::tag($this->config['cache_tag'])->set($cache_key, $user_info, $this->config['expire']);
        }
        return $user_info;
    }

    /**
     * 获取用户菜单
     * @return array|mixed
     * @author 原点 <467490186@qq.com>
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getMenuList()
    {
        $super_admin = $this->uid == 1 ? true : false;
        //获取缓存开启状态
        $is_cache = $this->config['is_cache'];
        $cache_key = '';
        //判断是否开启缓存
        if ($is_cache) {
            //设置缓存name
            if (!$super_admin) {
                $cache_key = $this->config['prefix'] . 'menuList_' . implode('-', $this->group_id);
            } else {
                $cache_key = $this->config['prefix'] . 'menuList_super';
            }
            //获取缓存数据
            $menuList = Cache::get($cache_key);
            if ($menuList) {
                return $menuList;
            }
        }
        if (!$super_admin) { //不是超级管理员，根据用户组获取对应的菜单
            $menuList = $this->_auth_rule(true);//获取规则
            $menuList = list_to_tree($menuList);
        } else {
            //超级管理员
            $map = [
                ['menu', '=', 1],
                ['status', '=', 1]
            ];
            $menuList = \think\Db::name($this->config['auth_rule'])->where($map)->order('sort desc')->select();
            $menuList = list_to_tree($menuList);
        }
        if ($is_cache) {
            //设置缓存数据
            Cache::tag($this->config['cache_tag'])->set($cache_key, $menuList, $this->config['expire']);
        }
        return $menuList;
    }

    public function _auth_rule($menu = false)
    {
        $groups = $this->getGroups();
        $ids = []; //保存用户所属用户组设置的所有权限规则id
        foreach ($groups as $g) {
            $ids = array_merge($ids, explode(',', trim($g['rules'], ',')));
        }
        $ids = array_unique($ids);
        if (empty($ids)) {
            return [];
        }
        //读取用户组所有权限规则
        $query = Db::name($this->config['auth_rule']);
        $query->where('id', 'in', $ids);
        $query->where('status', '=', 1);
        if ($menu) {
            $query->where('menu', '=', 1);
        }
        $query->order('sort desc');
        $rules = $query->select();
        //循环规则，判断结果。
        $menuList = []; //
        foreach ($rules as $rule) {
            if (!empty($rule['condition'])) {
                //根据condition进行验证
                $user = $this->getUserInfo($this->uid); //获取用户信息,一维数组
                $command = preg_replace('/\{(\w*?)\}/', '$user[\'\\1\']', $rule['condition']);
                $condition = '';
                @(eval('$condition=(' . $command . ');'));
                if ($condition) {
                    $menuList[] = $rule;
                }
            } else {
                //只要存在就记录
                $menuList[] = $rule;
            }
        }
        return $menuList;
    }
}