<?php
/**
 * Created by PhpStorm.
 * User: zhuangjun
 * Date: 2018/9/9
 * Time: 9:10
 */

namespace app\admin\service;

use app\admin\traits\Result;
use app\admin\model\AuthGroup;
class AuthGroupService
{
    use Result;

    /**
     * 添加用户组
     * @param $title 用户组名称
     */
    public static function add($title)
    {
        $group=new AuthGroup;
        $group->title=$title;
        $res=$group->save();
        if($res){
            $mag=Result::success('添加成功');
        }else{
            $mag=Result::error('添加失败');
        }
        return $mag;
    }

    /**
     * 编辑用户组
     * @param $id    用户组id
     * @param $data  修改数据
     */
    public static function edit($id,$data)
    {
        if(!$id || !$data)Result::error('参数错误');
        $group=new AuthGroup;
        $res=$group->save($data,['id'=>$id]);
        if($res){
            $mag=Result::success('编辑成功');
        }else{
            $mag=Result::error('编辑失败');
        }
        return $mag;
    }
}