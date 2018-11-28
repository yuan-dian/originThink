<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/28
 * Time: 13:20
 */

namespace app\admin\controller;
use app\admin\model\AuthRule;
use app\admin\model\Config;
use app\admin\model\LoginLog;
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
            $data = input();
            if( isset( $data['path'] )){
                $file = App::getRuntimePath();
                foreach ( $data['path'] as $key => $value ){
                    array_map('unlink', glob($file.$value . '/*.*'));
                    $dirs = (array) glob($file.$value . '/*');
                    foreach ($dirs as $dir) {
                        array_map('unlink', glob($dir . '/*'));
                    }
                    if ( $dirs && $data['delete'] ){
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
     * @throws \think\exception\DbException
     * @author 原点 <467490186@qq.com>
     */
    public function loginLog()
    {
        if(request()->isAjax()){
            $data = [
                'starttime' => $this->request->get('starttime','','trim'),
                'endtime'   => $this->request->get('endtime','','trim'),
                'key'       => $this->request->get('key','','trim'),
                'limit'     => $this->request->get('limit',10,'intval')
            ];
            $list = LoginLog::withSearch(['name','create_time'], [
                'name'			=>	$data['key'],
                'create_time'	=>	[ $data['starttime'] , $data['endtime'] ],
            ])->paginate( $data['limit'],false , ['query' => $data] );
            return (['code'=>0,'mag'=>'','data'=>$list->items(),'count'=>$list->total()]);
        }
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
            $list = AuthRule::order('sort desc')->select();
            return $result = ['code'=>0,'msg'=>'获取成功!','data'=>$list];
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
     */
    public function editMenu()
    {
        if( request()->isPost() ){
            $data=[
                'name'  =>  $this->request->post('name','','trim'),
                'title' =>  $this->request->post('title','','trim'),
                'pid'   =>  $this->request->post('pid',0,'intval'),
                'status'=>  $this->request->post('status',0,'intval'),
                'menu'  =>  $this->request->post('menu','','trim'),
                'icon'  =>  $this->request->post('icon','','trim'),
                'sort'  =>  $this->request->post('sort',0,'intval'),
            ];
            $id = $this->request->post('id',0,'intval');
            if( $id ){
                $res=AuthRule::where('id','=',$id)->update($data);
            }else{
                $res=AuthRule::create($data);
            }
            if( $res ){
                Cache::clear(config('auth.cache_tag'));//清除Auth类设置的缓存
                $this->success('保存成功',url('/admin/menu'));
            }else{
                $this->error('保存失败');
            }
        }else{
            $id = $this->request->param('id',0,'intval');
            if( $id ){
                $data = AuthRule::where('id','=',$id)->find();
                $this->assign('data',$data);
            }
            $menu = AuthRule::where('pid','=',0)->order('sort desc')->column('id,title');
            $menu[0] ='顶级菜单';
            ksort($menu);
            $this->assign('menu',$menu);
            return $this->fetch();
        }
    }

    /**
     * 删除菜单
     * @author 原点 <467490186@qq.com>
     * @throws \Exception
     */
    public function deleteMenu()
    {
        $id = $this->request->post('id',0,'intval');
        empty($id) && $this->error('参数错误');
        if( AuthRule::where('pid' ,'=', $id)->count()>0 ){
            $this->error('该菜单存在子菜单,无法删除!');
        }
        $res = AuthRule::where('id', '=', $id)->delete();
        if( $res ){
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
        if( !request()->isPost() ){
            $data = Config::where('name','system_config')->find();
            $this->assign('data',$data);
            return $this->fetch();
        }else{
            $save = [
                'value' => [
                    'debug'      => $this->request->post('debug',0,'intval'),
                    'trace'      => $this->request->post('trace',0,'intval'),
                    'trace_type' => $this->request->post('trace_type',0,'intval'),
                ],
                'status' => $this->request->post('status',0,'intval')
            ];
            $res = Config::update($save,['name'=>'system_config']);
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
     * @return array|mixed|\PDOStatement|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function siteConfig()
    {
        if(!request()->isPost()){
            $data = Config::where('name','site_config')->find();
            $this->assign('data',$data);
            return $this->fetch();
        }else{
            $save = [
                'value' => [
                    'title'     => $this->request->post('title','','trim'),
                    'name'      => $this->request->post('name','','trim'),
                    'copyright' => $this->request->post('copyright','','trim'),
                    'icp'       => $this->request->post('icp','','trim')
                ],
            ];
            $res = Config::update($save,['name' => 'site_config']);
            if( $res ){
                cache('site_config',null);
                $this->success('修改成功',url('/admin/siteConfig'));
            }else{
                $this->error('修改失败');
            }
        }
    }
}