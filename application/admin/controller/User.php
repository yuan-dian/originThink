<?php
/**
 * Created by originThink
 * Author: 原点 467490186@qq.com
 * Date: 2018/1/18
 * Time: 15:05
 */

namespace app\admin\controller;


use app\admin\model\AuthGroup;
use app\admin\model\AuthGroupAccess;
use app\admin\model\AuthRule;
use app\admin\service\UserService;
use app\admin\service\AuthGroupService;
use app\admin\model\User as UserModel;

class User extends Common
{
    /**
     * 用户列表
     * @return mixed
     * @throws \think\exception\DbException
     * @author 原点 <467490186@qq.com>
     */
    public function userList()
    {
        if ($this->request->isAjax()) {
            $data = [
                'key' => $this->request->get('key', '', 'trim'),
                'limit' => $this->request->get('limit', 10, 'intval'),
            ];
            $list = UserModel::withSearch(['name'], ['name' => $data['key']])
                ->hidden(['password'])
                ->paginate($data['limit'], false, ['query' => $data]);
            $user_date = [];
            foreach ($list as $key => $val) {
                $user_date[$key] = $val;
                $user_date[$key]['title'] = $val->group_titles;
            }
            return show($user_date, 0, '', ['count' => $list->total()]);
        }
        return $this->fetch();
    }

    /**
     * 获取用户信息
     * @return mixed|void
     */
    public function userInfo()
    {
        if (!$this->request->isAjax()) {
            $info = UserModel::get($this->uid)->hidden(['password']);
            $info['group_titles'] = $info->group_titles;
            $info['head'] ? : $info['head'] = '/images/face.jpg';
            $this->assign('info', $info);
            return $this->fetch();
        } else {
            $data = [
                'name' => $this->request->post('name'),
                'head' => $this->request->post('head'),
            ];
            $res = UserModel::update($data, ['uid' => $this->uid]);
            if ($res) {
                $code = 1;
                $msg = '保存成功';
            }else{
                $code = 0;
                $msg = '保存失败';
            }
            return show([], $code, $msg);
        }

    }

    /**
     * 添加、编辑用户
     * @return mixed
     * @author 原点 <467490186@qq.com>
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function edit()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if ($data['uid']) {
                //编辑
                $res = UserService::edit($data);
                return $res;
            } else {
                //添加
                $data = UserService::add($data);
                return $data;
            }
        } else {
            $uid = $this->request->get('uid', 0, 'intval');
            if ($uid) {
                $list = UserModel::where('uid', '=', $uid)->hidden(['password'])->find();
                $list['group_id'] = AuthGroupAccess::where('uid', '=', $uid)->column('group_id');
                $this->assign('list', $list);
            }
            $grouplist = AuthGroup::column('id,title');
            $this->assign('grouplist', $grouplist);
            return $this->fetch();
        }
    }

    /**
     * 验证用户名是否存在
     * @return array
     * @throws \think\exception\DbException
     * @author 原点 <467490186@qq.com>
     */
    public function check()
    {
        $username = $this->request->get('username', '', 'trim');
        $res = UserModel::where('user', '=', $username)->field('uid')->find();
        if ($res) {
            $msg = ['code' => 1, 'msg' => '账号已存在'];
        } else {
            $msg = ['status' => 0, 'info' => '验证通过'];
        }
        return $msg;
    }

    /**
     * 删除用户
     * @author 原点 <467490186@qq.com>
     */
    public function delete()
    {
        $uid = $this->request->param('uid', 0, 'intval');
        if ($uid) {
            if ($uid != 1) {
                $res = UserService::delete($uid);
                return $res;
            } else {
                $this->error('无法删除超级管理员');
            }
        } else {
            $this->error('参数错误');
        }
    }

    /**
     * 用户组管理
     * @return array|mixed
     * @author 原点 <467490186@qq.com>
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function groupList()
    {
        if ($this->request->isAjax()) {
            $key = $this->request->get('key', '', 'trim');
            $limit = $this->request->get('key', 10, 'intval');
            $map = [];
            empty ($key) || $map[] = ['title', 'like', '%' . $key . '%'];
            $list = AuthGroup::where($map)->paginate($limit, false, ['query' => ['key' => $key], 'limit' => $limit]);
            return show($list->items(), 0, '', ['count' => $list->total()]);
        }
        return $this->fetch();
    }

    /**
     * 添加编辑用户组
     * @return array|string
     */
    public function editGroup()
    {
        if ($this->request->isPost()) {
            $id = $this->request->post('id', 0, 'intval');
            $title = $this->request->post('title', '', 'trim');
            if ($id) {//编辑用户组
                return AuthGroupService::edit($id, ['title' => $title]);
            } else {//添加用户组
                return AuthGroupService::add($title);
            }
        } else {
            $this->error('非法请求');
        }
    }

    /**
     * 禁用用户组
     * @return array|string
     */
    public function disableGroup()
    {
        if ($this->request->isPost()) {
            $id = $this->request->post('id', 0, 'intval');
            $status = $this->request->post('status', 0, 'intval');
            return AuthGroupService::edit($id, ['status' => $status]);
        } else {
            $this->error('非法请求');
        }
    }

    /**
     * 获取权限列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function ruleList()
    {
        $list = AuthRule::field('id,pid,title as text')->select();
        $data = list_to_tree($list->toArray(), 'id', 'pid', 'children');
        return $data;
    }

    /**
     * 修改用户组权限
     * @return array|string
     */
    public function editRule()
    {
        if ($this->request->isPost()) {
            $id = $this->request->post('id', 0, 'intval');
            $rules = $this->request->post('rules', []);
            if (!$rules) $this->error('参数错误');
            sort($rules);
            $rules = implode(',', $rules);
            $res = AuthGroupService::edit($id, ['rules' => $rules], true);
            return $res;
        } else {
            $this->error('非法请求');
        }
    }

    /**
     * 修改密码
     * @return array|mixed
     * @author 原点 <467490186@qq.com>
     * @throws \think\Exception\DbException
     */
    public function editPassword()
    {
        if ($this->request->isPost()) {
            $data = input();
            $user = session('user_auth');
            $res = UserService::editPassword($user['uid'], $data['oldpassword'], $data['password']);
            return $res;
        } else {
            return $this->fetch();
        }
    }
}