APP接口校验
===============

主要特性包括：

 + 基于header校验
 + 签名校验
 + 时间校验
 + 设备校验
 + 请求唯一性校验


> 需要ThinkPHP5.1.6+

## 使用
1、配置config/app.php文件
~~~
// 异常处理handle类 
'exception_handle'       => 'check\exception\ApiHandleException',
~~~
2、在路由中使用
~~~
Route::get('index','index/index/index')->middleware(check\middleware\Check::class);
~~~

3、在控制器中使用

~~~
<?php
namespace app\index\controller;

use think\Controller;

class Index extends Controller
{
    protected $middleware = ['check\\middleware\\Check'];

    public function index()
    {
        return 'index';
    }
}
~~~
4、在配置文件中配置相关参数
~~~
    1、在confing文件夹中新建api.php文件，配置示例
    <?php
    return [
        'key' => '863b42d64d68e913',       // 加密key
        'cipher' => 'AES-256-CBC',         // 加密算法
        'iv' => '9410b305637c69eb',        // 加密向量
        'apptypes' => ['ios', 'android'],  // APP类型
        'app_sign_time' => 90,             // sign失效时间
        'app_sign_cache_time' => 20,       // sign 缓存失效时间
    ];
~~~

## 目录结构


~~~
extent  extent扩展目录
├─check           check目录
│  ├─exception             异常处理目录
│  │  ├─ApiException.php      接口异常处理文件
│  │  ├─ApiHandleException.php      接口异常输出
│  ├─middleware        中间件目录
│  │  ├─Check.php      中间件文件
│  ├─Aes.php        AES加密类文件

