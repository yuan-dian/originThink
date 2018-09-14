<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/28
 * Time: 13:20
 */

namespace app\admin\controller;
use think\facade\App;
use think\facade\Cache;
class System extends Common
{
    /**
     * 清除缓存
     * @author 原点 <467490186@qq.com>
     */
    public function cleanCache()
    {

        if(!request()->isPost()){
            return $this->fetch();
        }else{
            $data=input();
            if(isset($data['path'])){

                $file=App::getRuntimePath();
                foreach ($data['path'] as $key=>$value){
                    array_map('unlink', glob($file.$value . '/*.*'));
                    $dirs = (array) glob($file.$value . '/*');
                    foreach ($dirs as $dir) {
                        array_map('unlink', glob($dir . '/*'));
                    }
                    if($dirs && $data['delete']){
                        array_map('rmdir', $dirs);
                    }
                }
                $this->success('缓存清空成功');
            }else{
                $this->error('请选择清除的范围');
            }
        }
    }

    /**
     * 登录日志
     * @return mixed
     * @author 原点 <467490186@qq.com>
     */
    public function loginLog()
    {
        $list=[];
        if(request()->isAjax()){
            $data=input();
            $map=[];
            if(isset($data['starttime']) && isset($data['endtime'])){
                if($data['starttime'] && $data['endtime']){
                    $map[]=['create_time', 'between time', [$data['starttime'], $data['endtime']]];
                }
            }
            empty($data['key']) || $map[]=['user|name','like','%'.$data['key'].'%'];
            isset($data['limit'])?$limit=$data['limit'] : $limit=10;
            $list=db('login_log')->where($map)->withAttr('create_time', function($value, $data) {
                return date('Y-m-d H:i:s',$value);
            })->fetchSql(false)->paginate($limit,false,['query'=>$data]);
            $data=$list->toarray();
            return (['code'=>0,'mag'=>'','data'=>$data['data'],'count'=>$data['total']]);
        }
        $this->assign('list',$list);
        return $this->fetch();
    }

    /**
     *系统菜单
     * @return mixed
     * @author 原点 <467490186@qq.com>
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function menu()
    {
        if(request()->isPost()){
            $list=db('auth_rule')->order('sort desc')->select();
            return $result = ['code'=>0,'msg'=>'获取成功!','data'=>$list,'is'=>true,'tip'=>'操作成功'];
        }
        return $this->fetch();
    }

    /**
     * 菜单编辑
     * @author 原点 <467490186@qq.com>
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function editMenu()
    {
        if(request()->isPost()){
            $data=input();
            $save=[
                'name'=>$data['name'],
                'title'=>$data['title'],
                'pid'=>$data['pid'],
                'status'=>$data['status'],
                'menu'=>$data['menu'],
                'icon'=>$data['icon'],
                'sort'=>$data['sort'],
            ];
            if(isset($data['id']) && $data['id']){
                $res=db('auth_rule')->where('id',$data['id'])->update($save);
            }else{
                $res=db('auth_rule')->insert($save);
            }
            if($res){
                Cache::clear(config('auth.cache_tag'));//清除Auth类设置的缓存
                $this->success('保存成功',url('/admin/menu'));
            }else{
                $this->error('保存失败');
            }
        }else{
            $id=input('get.id','','intval');
            if($id){
                $data=db('auth_rule','',false)->where('id','=',$id)->find();
                $this->assign('data',$data);
            }
            $menu=db('auth_rule','',false)->where('pid','=',0)->order('sort desc')->column('id,title');
            $menu[0]='顶级菜单';
            ksort($menu);
            $this->assign('menu',$menu);
            return $this->fetch();
        }
    }

    /**
     * 删除菜单
     * @author 原点 <467490186@qq.com>
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function deleteMenu()
    {
        $data=input();
        isset($data['id']) || $this->error('参数错误');
        if(db('auth_rule','',false)->where('pid',$data['id'])->count()>0){
            $this->error('该菜单存在子菜单,无法删除!');
        }
        $res=db('auth_rule','',false)->where('id',$data['id'])->delete();
        if($res){
            Cache::clear(config('auth.cache_tag'));//清除Auth类设置的缓存
            $this->success('删除成功',url('/admin/menu'));
        }else{
            $this->error('删除失败');
        }
    }
    /**
     * 配置管理
     * @return mixed|void
     * @author 原点 <467490186@qq.com>
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function config()
    {
        if(!request()->isPost()){
            $data=db('config')->where('name','system_config')->json(['value'])->find();
            $this->assign('data',$data);
            return $this->fetch();
        }else{
            $data=input();
            if(!$data) $this->error('参数错误');
            $save=[
                'value'=>[
                    'debug'=>$data['debug'],
                    'trace'=>$data['trace'],
                    'trace_type'=>$data['trace_type'],
                ],
                'status'=>$data['status'],
                'update_time'=>time()
            ];
            $res=db('config')->where('name','system_config')->json(['value'])->update($save);
            if($res){
                cache('config',null);
                $this->success('修改成功',url('/admin/config'));
            }else{
                $this->error('修改失败');
            }
        }

    }

    /**
     * 站点配置
     * @return mixed
     */
    public function siteConfig()
    {
        if(!request()->isPost()){
            $data=db('config')->where('name','site_config')->json(['value'])->find();
            $this->assign('data',$data);
            return $this->fetch();
        }else{
            $title=input('title','tpswoole','trim');
            $name=input('name','tpswoole','trim');
            $copyright=input('copyright','copyright @2018 原点','trim');
            $icp=input('icp','copyright @2018 原点','trim');
            if(!$title || !$name)$this->error('参数错误');
            $save=[
                'value'=>[
                    'title'=>$title,
                    'name'=>$name,
                    'copyright'=>$copyright,
                    'icp'=>$icp
                ],
                'update_time'=>time()
            ];
            $res=db('config')->where('name','site_config')->json(['value'])->update($save);
            if($res){
                cache('site_config',null);
                $this->success('修改成功',url('/admin/siteConfig'));
            }else{
                $this->error('修改失败');
            }
        }
    }
}