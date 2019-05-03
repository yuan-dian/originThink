##系统要求
 + php5.6+
 + ThinkPHP5.1.6+

##安装步骤：

 + 第一步：
    + Composer方式：
     ~~~
        Composer install 或 Composer update
     ~~~
     
    + 手动下载ThinkPHP核心包，命名为thinkphp，放入根目录，推荐下载[releases包](https://github.com/top-think/framework/releases)
 + 第二步：执行SQL文件（根目录sql.sql文件）
 
 ##验证码：
 + 安装think-captcha扩展包
 
  ~~~
     composer require topthink/think-captcha=2.*
  ~~~
 
 + 配置验证码开启状态：应用配置目录captcha.php文件
 
 ~~~
    'is_open' => true
 ~~~
 
 
##默认账号密码
 + 默认账号：admin
 + 密码：123456

##核心内容
 + 采用ThinkPHP5.1框架
 + 前端采用layui构建
 + 用户管理
 + 配置管理
 + 菜单管理
 + 缓存管理
 + 权限管理
 + API接口校验
 
##感谢
 + ThinkPHP
 + layui
 + layuicms

##交流
QQ交流群：1004068839
 
 ##捐献
![](http://blog.zhuangjun.top/images/wx_reward.png) 
![](http://blog.zhuangjun.top/images/ali_reward.png) 

 ##捐献墙
 + 2019-04-23 17:17   10元   姓名未知    支付备注：感谢开源，学习学习 
 + 2019-04-23 19:20   15元   姓名:Kevin  支付备注：感谢原点兄....
