<?php
/**
 *
 * User: Edwin
 * Date: 2019/5/31
 * Email: <467490186@qq.com>
 */

namespace app\admin\controller;


class Upload extends Common
{
    public function index()
    {
        $file = request()->file('file');
        $info = $file->validate(['size'=>1024*1024*2,'ext'=>'jpg,png,gif'])->move( '../public/uploads');
        if($info){
            $msg=['code'=>0,'msg'=>'上传成功','data'=>['src'=>'/uploads/'.$info->getSaveName()]];
        }else{
            $msg=['code'=>1,'msg'=>$file->getError()];
        }
        return $msg;
        
    }

}