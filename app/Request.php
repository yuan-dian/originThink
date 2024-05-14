<?php

namespace app;

// 应用请求对象类
class Request extends \think\Request
{
    public function path()
    {
        $dirname = pathinfo($this->pathinfo, PATHINFO_DIRNAME);
        $basename = pathinfo($this->pathinfo, PATHINFO_FILENAME);

        return $dirname . '/' . $basename;
    }

}
