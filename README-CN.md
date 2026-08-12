<div align="center">
    <img src="https://github.com/hhxsv5/laravel-s/raw/PHP-8.x/logo.svg" alt="LaravelS Logo" height="80">
     <p>
        <a href="https://github.com/hhxsv5/laravel-s/blob/PHP-8.x/README-CN.md">中文文档</a> |
        <a href="https://github.com/hhxsv5/laravel-s/blob/PHP-8.x/README.md">English Docs</a>
    </p>
    <p>🚀 LaravelS 是 Laravel/Lumen 和 Swoole 之间开箱即用的适配器</p>
    <p>
        <a href="https://github.com/hhxsv5/laravel-s/releases">
            <img src="https://img.shields.io/github/v/release/hhxsv5/laravel-s?filter=v3.8.*" alt="Latest Version">
        </a>
        <a href="https://www.php.net/">
            <img src="https://img.shields.io/packagist/php-v/hhxsv5/laravel-s" alt="PHP Version">
        </a>
        <a href="https://github.com/swoole/swoole-src">
            <img src="https://img.shields.io/badge/swoole-%3E=5.0.0-flat.svg" alt="Swoole Version">
        </a>
        <a href="https://packagist.org/packages/hhxsv5/laravel-s/stats">
            <img src="https://img.shields.io/packagist/dt/hhxsv5/laravel-s" alt="Total Downloads">
        </a>
        <a href="https://scrutinizer-ci.com/g/hhxsv5/laravel-s/">
            <img src="https://scrutinizer-ci.com/g/hhxsv5/laravel-s/badges/build.png?b=PHP-8.x" alt="Build Status">
        </a>
        <a href="https://scrutinizer-ci.com/g/hhxsv5/laravel-s/">
            <img src="https://scrutinizer-ci.com/g/hhxsv5/laravel-s/badges/code-intelligence.svg?b=PHP-8.x" alt="Code Intelligence Status">
        </a>
        <a href="https://github.com/hhxsv5/laravel-s/blob/PHP-8.x/LICENSE">
            <img src="https://img.shields.io/github/license/hhxsv5/laravel-s" alt="License">
        </a>
    </p>
</div>

---

## 持续更新
- *请`Watch`此仓库，以获得最新的更新。*
- **QQ交流群**：
  - `698480528` [![点击加群](https://pub.idqqimg.com/wpa/images/group.png "点击加群")](//shang.qq.com/wpa/qunwpa?idkey=f949191c8f413a3ecc5fbce661e57d379740ba92172bd50b02d23a5ab36cc7d6)
  - `62075835` [![点击加群](https://pub.idqqimg.com/wpa/images/group.png "点击加群")](//shang.qq.com/wpa/qunwpa?idkey=5230f8da0693a812811e21e19d5823ee802ee5d24def177663f42a32a9060e97)

文档目录
=================

* [特性](#特性)
* [Benchmark](#benchmark)
* [要求](#要求)
* [安装](#安装)
* [运行](#运行)
* [部署](#部署)
* [与Nginx配合使用（推荐）](#与nginx配合使用推荐)
* [与Apache配合使用](#与apache配合使用)
* [启用WebSocket服务器](#启用websocket服务器)
* [监听事件](#监听事件)
    * [系统事件](#系统事件)
    * [自定义的异步事件](#自定义的异步事件)
* [异步的任务队列](#异步的任务队列)
* [毫秒级定时任务](#毫秒级定时任务)
* [修改代码后自动Reload](#修改代码后自动reload)
* [在你的项目中使用SwooleServer实例](#在你的项目中使用swooleserver实例)
* [使用SwooleTable](#使用swooletable)
* [多端口混合协议](#多端口混合协议)
* [协程](#协程)
* [自定义进程](#自定义进程)
* [常用组件](#常用组件)
    * [Apollo](#apollo)
    * [Prometheus](#prometheus)
* [其他特性](#其他特性)
    * [配置Swoole事件](#配置Swoole事件)
    * [Serverless](#serverless)
* [注意事项](#注意事项)
* [用户与案例](#用户与案例)
* [其他选择](#其他选择)
* [赞助](#赞助)
    * [感谢](#感谢)
* [Star历史](#star历史)
* [License](#license)

## 特性

- 内置Http/[WebSocket](https://github.com/hhxsv5/laravel-s/blob/PHP-8.x/README-CN.md#%E5%90%AF%E7%94%A8websocket%E6%9C%8D%E5%8A%A1%E5%99%A8)服务器

- [多端口混合协议](https://github.com/hhxsv5/laravel-s/blob/PHP-8.x/README-CN.md#%E5%A4%9A%E7%AB%AF%E5%8F%A3%E6%B7%B7%E5%90%88%E5%8D%8F%E8%AE%AE)

- [自定义进程](https://github.com/hhxsv5/laravel-s/blob/PHP-8.x/README-CN.md#%E8%87%AA%E5%AE%9A%E4%B9%89%E8%BF%9B%E7%A8%8B)

- 常驻内存

- [异步的事件监听](https://github.com/hhxsv5/laravel-s/blob/PHP-8.x/README-CN.md#%E8%87%AA%E5%AE%9A%E4%B9%89%E7%9A%84%E5%BC%82%E6%AD%A5%E4%BA%8B%E4%BB%B6)

- [异步的任务队列](https://github.com/hhxsv5/laravel-s/blob/PHP-8.x/README-CN.md#%E5%BC%82%E6%AD%A5%E7%9A%84%E4%BB%BB%E5%8A%A1%E9%98%9F%E5%88%97)

- [毫秒级定时任务](https://github.com/hhxsv5/laravel-s/blob/PHP-8.x/README-CN.md#%E6%AF%AB%E7%A7%92%E7%BA%A7%E5%AE%9A%E6%97%B6%E4%BB%BB%E5%8A%A1)

- [常用组件](https://github.com/hhxsv5/laravel-s/blob/PHP-8.x/README-CN.md#%E5%B8%B8%E7%94%A8%E7%BB%84%E4%BB%B6)

- 平滑Reload

- [修改代码后自动Reload](https://github.com/hhxsv5/laravel-s/blob/PHP-8.x/README-CN.md#%E4%BF%AE%E6%94%B9%E4%BB%A3%E7%A0%81%E5%90%8E%E8%87%AA%E5%8A%A8reload)

- 同时支持Laravel与Lumen，兼容主流版本

- 简单，开箱即用

## Benchmark

- [Which is the fastest web framework?](https://github.com/the-benchmarker/web-frameworks)

- [TechEmpower Framework Benchmarks](https://www.techempower.com/benchmarks/)

## 要求

| 依赖 | 说明 |
| -------- | -------- |
| [PHP](https://www.php.net/) | `>=8.2` `启用扩展intl` |
| [Swoole](https://www.swoole.com/) | `>=5.0` |
| [Laravel](https://laravel.com/)/[Lumen](https://lumen.laravel.com/) | `>=10` |

## 安装

1.通过[Composer](https://getcomposer.org/)安装([packagist](https://packagist.org/packages/hhxsv5/laravel-s))。有可能找不到`3.0`版本，解决方案移步[#81](https://github.com/hhxsv5/laravel-s/issues/81)。
```bash
# PHP >=8.2
composer require "hhxsv5/laravel-s:~3.8.0"

# PHP >=5.5.9,<=7.4.33
# composer require "hhxsv5/laravel-s:~3.7.0"

# 确保你的composer.lock文件是在版本控制中
```

2.注册Service Provider（以下两步二选一）。

- `Laravel`: 修改文件`config/app.php`，`Laravel 5.5+支持包自动发现，你应该跳过这步`
    ```php
    'providers' => [
        //...
        Hhxsv5\LaravelS\Illuminate\LaravelSServiceProvider::class,
    ],
    ```

- `Lumen`: 修改文件`bootstrap/app.php`
    ```php
    $app->register(Hhxsv5\LaravelS\Illuminate\LaravelSServiceProvider::class);
    ```

3.发布配置和二进制文件。
> *每次升级LaravelS后，需重新publish；点击[Release](https://github.com/hhxsv5/laravel-s/releases)去了解各个版本的变更记录。*

```bash
php artisan laravels publish
# 配置文件：config/laravels.php
# 二进制文件：bin/laravels bin/fswatch bin/inotify
```

4.修改配置`config/laravels.php`：监听的IP、端口等，请参考[配置项](https://github.com/hhxsv5/laravel-s/blob/PHP-8.x/Settings-CN.md)。

5.性能调优

- [调整内核参数](https://wiki.swoole.com/#/other/sysctl?id=%e5%86%85%e6%a0%b8%e5%8f%82%e6%95%b0%e8%b0%83%e6%95%b4)

- [Worker数量](https://wiki.swoole.com/#/server/setting?id=worker_num)：LaravelS 使用 Swoole 的`同步IO`模式，`worker_num`设置的越大并发性能越好，但会造成更多的内存占用和进程切换开销。如果`1`个请求耗时`100ms`，为了提供`1000QPS`的并发能力，至少需要配置`100`个Worker进程，计算方法：worker_num = 1000QPS/(1s/1ms) = 100，故需进行增量压测计算出最佳的`worker_num`。

- [Task Worker数量](https://wiki.swoole.com/#/server/setting?id=task_worker_num)


## 运行
> `在运行之前，请先仔细阅读：`[注意事项](https://github.com/hhxsv5/laravel-s/blob/PHP-8.x/README-CN.md#%E6%B3%A8%E6%84%8F%E4%BA%8B%E9%A1%B9)(这非常重要)。

- 操作命令：`php bin/laravels {start|stop|restart|reload|info|help}`。

| 命令 | 说明 |
| --------- | --------- |
| start | 启动LaravelS，展示已启动的进程列表 "*ps -ef&#124;grep laravels*" |
| stop | 停止LaravelS，并触发自定义进程的`onStop`方法 |
| restart | 重启LaravelS：先平滑`Stop`，然后再`Start`；在`Start`完成之前，服务是`不可用的` |
| reload | 平滑重启所有Task/Worker/Timer进程(这些进程内包含了你的业务代码)，并触发自定义进程的`onReload`方法，不会重启Master/Manger进程；修改`config/laravels.php`后，你`只有`调用`restart`来完成重启 |
| info | 显示组件的版本信息 |
| help | 显示帮助信息 |

- 启动选项，针对`start`和`restart`命令。

| 选项 | 说明 |
| --------- | --------- |
| -d&#124;--daemonize | 以守护进程的方式运行，此选项将覆盖`laravels.php`中`swoole.daemonize`设置 |
| -e&#124;--env | 指定运行的环境，如`--env=testing`将会优先使用配置文件`.env.testing`，这个特性要求`Laravel 5.2+` |
| -i&#124;--ignore | 忽略检查Master进程的PID文件 |
| -x&#124;--x-version | 记录当前工程的版本号(分支)，保存在`$_ENV`/`$_SERVER`中，访问方式：`$_ENV['X_VERSION']` `$_SERVER['X_VERSION']` `$request->server->get('X_VERSION')` |

- `运行时`文件：`start`时会自动执行`php artisan laravels config`并生成这些文件，开发者一般不需要关注它们，建议将它们加到`.gitignore`中。

| 文件 | 说明 |
| --------- | --------- |
| storage/laravels.conf | LaravelS的`运行时`配置文件 |
| storage/laravels.pid | Master进程的PID文件 |
| storage/laravels-timer-process.pid | 定时器Timer进程的PID文件 |
| storage/laravels-custom-processes.pid | 所有自定义进程的PID文件 |

## 部署
> 建议通过[Supervisord](http://supervisord.org/)监管主进程，前提是不能加`-d`选项并且设置`swoole.daemonize`为`false`。

```
[program:laravel-s-test]
directory=/var/www/laravel-s-test
command=/usr/local/bin/php bin/laravels start -i
numprocs=1
autostart=true
autorestart=true
startretries=3
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/%(program_name)s.log
```

## 与Nginx配合使用（推荐）
> [示例](https://github.com/hhxsv5/docker/blob/master/nginx/conf.d/laravels.conf)。

```nginx
gzip on;
gzip_min_length 1024;
gzip_comp_level 2;
gzip_types text/plain text/css text/javascript application/json application/javascript application/x-javascript application/xml application/x-httpd-php image/jpeg image/gif image/png font/ttf font/otf image/svg+xml;
gzip_vary on;
gzip_disable "msie6";
upstream swoole {
    # 通过 IP:Port 连接
    server 127.0.0.1:5200 weight=5 max_fails=3 fail_timeout=30s;
    # 通过 UnixSocket Stream 连接，小诀窍：将socket文件放在/dev/shm目录下，可获得更好的性能
    #server unix:/yourpath/laravel-s-test/storage/laravels.sock weight=5 max_fails=3 fail_timeout=30s;
    #server 192.168.1.1:5200 weight=3 max_fails=3 fail_timeout=30s;
    #server 192.168.1.2:5200 backup;
    keepalive 16;
}
server {
    listen 80;
    # 别忘了绑Host
    server_name laravels.com;
    root /yourpath/laravel-s-test/public;
    access_log /yourpath/log/nginx/$server_name.access.log  main;
    autoindex off;
    index index.html index.htm;
    # Nginx处理静态资源(建议开启gzip)，LaravelS处理动态资源。
    location / {
        try_files $uri @laravels;
    }
    # 当请求PHP文件时直接响应404，防止暴露public/*.php
    #location ~* \.php$ {
    #    return 404;
    #}
    location @laravels {
        # proxy_connect_timeout 60s;
        # proxy_send_timeout 60s;
        # proxy_read_timeout 120s;
        proxy_http_version 1.1;
        proxy_set_header Connection "";
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Real-PORT $remote_port;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Host $http_host;
        proxy_set_header Scheme $scheme;
        proxy_set_header Server-Protocol $server_protocol;
        proxy_set_header Server-Name $server_name;
        proxy_set_header Server-Addr $server_addr;
        proxy_set_header Server-Port $server_port;
        # “swoole”是指upstream
        proxy_pass http://swoole;
    }
}
```

## 与Apache配合使用

```apache
LoadModule proxy_module /yourpath/modules/mod_proxy.so
LoadModule proxy_balancer_module /yourpath/modules/mod_proxy_balancer.so
LoadModule lbmethod_byrequests_module /yourpath/modules/mod_lbmethod_byrequests.so
LoadModule proxy_http_module /yourpath/modules/mod_proxy_http.so
LoadModule slotmem_shm_module /yourpath/modules/mod_slotmem_shm.so
LoadModule rewrite_module /yourpath/modules/mod_rewrite.so
LoadModule remoteip_module /yourpath/modules/mod_remoteip.so
LoadModule deflate_module /yourpath/modules/mod_deflate.so

<IfModule deflate_module>
    SetOutputFilter DEFLATE
    DeflateCompressionLevel 2
    AddOutputFilterByType DEFLATE text/html text/plain text/css text/javascript application/json application/javascript application/x-javascript application/xml application/x-httpd-php image/jpeg image/gif image/png font/ttf font/otf image/svg+xml
</IfModule>

<VirtualHost *:80>
    # 别忘了绑Host
    ServerName www.laravels.com
    ServerAdmin hhxsv5@sina.com

    DocumentRoot /yourpath/laravel-s-test/public;
    DirectoryIndex index.html index.htm
    <Directory "/">
        AllowOverride None
        Require all granted
    </Directory>

    RemoteIPHeader X-Forwarded-For

    ProxyRequests Off
    ProxyPreserveHost On
    <Proxy balancer://laravels>  
        BalancerMember http://192.168.1.1:5200 loadfactor=7
        #BalancerMember http://192.168.1.2:5200 loadfactor=3
        #BalancerMember http://192.168.1.3:5200 loadfactor=1 status=+H
        ProxySet lbmethod=byrequests
    </Proxy>
    #ProxyPass / balancer://laravels/
    #ProxyPassReverse / balancer://laravels/

    # Apache处理静态资源，LaravelS处理动态资源。
    RewriteEngine On
    RewriteCond %{DOCUMENT_ROOT}%{REQUEST_FILENAME} !-d
    RewriteCond %{DOCUMENT_ROOT}%{REQUEST_FILENAME} !-f
    RewriteRule ^/(.*)$ balancer://laravels%{REQUEST_URI} [P,L]

    ErrorLog ${APACHE_LOG_DIR}/www.laravels.com.error.log
    CustomLog ${APACHE_LOG_DIR}/www.laravels.com.access.log combined
</VirtualHost>
```
## 启用WebSocket服务器
> WebSocket服务器监听的IP和端口与Http服务器相同。

1.创建WebSocket Handler类，并实现接口`WebSocketHandlerInterface`。start时会自动实例化，不需要手动创建实例。

```php
namespace App\Services;
use Hhxsv5\LaravelS\Swoole\WebSocketHandlerInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;
/**
 * @see https://wiki.swoole.com/#/start/start_ws_server
 */
class WebSocketService implements WebSocketHandlerInterface
{
    // 声明没有参数的构造函数
    public function __construct()
    {
    }
    // public function onHandShake(Request $request, Response $response)
    // {
           // 自定义握手：https://wiki.swoole.com/#/websocket_server?id=onhandshake
           // 成功握手之后会自动触发onOpen事件
    // }
    public function onOpen(Server $server, Request $request)
    {
        // 在触发onOpen事件之前，建立WebSocket的HTTP请求已经经过了Laravel的路由，
        // 所以Laravel的Request、Auth等信息是可读的，Session是可读写的，但仅限在onOpen事件中。
        // \Log::info('New WebSocket connection', [$request->fd, request()->all(), session()->getId(), session('xxx'), session(['yyy' => time()])]);
        // 此处抛出的异常会被上层捕获并记录到Swoole日志，开发者需要手动try/catch
        $server->push($request->fd, 'Welcome to LaravelS');
    }
    public function onMessage(Server $server, Frame $frame)
    {
        // \Log::info('Received message', [$frame->fd, $frame->data, $frame->opcode, $frame->finish]);
        // 此处抛出的异常会被上层捕获并记录到Swoole日志，开发者需要手动try/catch
        $server->push($frame->fd, date('Y-m-d H:i:s'));
    }
    public function onClose(Server $server, $fd, $reactorId)
    {
        // 此处抛出的异常会被上层捕获并记录到Swoole日志，开发者需要手动try/catch
    }
}
```

2.更改配置`config/laravels.php`。

```php
// ...
'websocket'      => [
    'enable'  => true, // 注意：设置enable为true
    'handler' => \App\Services\WebSocketService::class,
],
'swoole'         => [
    //...
    // dispatch_mode只能设置为2、4、5，https://wiki.swoole.com/#/server/setting?id=dispatch_mode
    'dispatch_mode' => 2,
    //...
],
// ...
```

3.使用`SwooleTable`绑定FD与UserId，可选的，[Swoole Table示例](#使用swooletable)。也可以用其他全局存储服务，例如Redis/Memcached/MySQL，但需要注意多个`Swoole Server`实例时FD可能冲突。

4.与Nginx配合使用（推荐）
> 参考 [WebSocket代理](http://nginx.org/en/docs/http/websocket.html)

```nginx
map $http_upgrade $connection_upgrade {
    default upgrade;
    ''      close;
}
upstream swoole {
    # 通过 IP:Port 连接
    server 127.0.0.1:5200 weight=5 max_fails=3 fail_timeout=30s;
    # 通过 UnixSocket Stream 连接，小诀窍：将socket文件放在/dev/shm目录下，可获得更好的性能
    #server unix:/yourpath/laravel-s-test/storage/laravels.sock weight=5 max_fails=3 fail_timeout=30s;
    #server 192.168.1.1:5200 weight=3 max_fails=3 fail_timeout=30s;
    #server 192.168.1.2:5200 backup;
    keepalive 16;
}
server {
    listen 80;
    # 别忘了绑Host
    server_name laravels.com;
    root /yourpath/laravel-s-test/public;
    access_log /yourpath/log/nginx/$server_name.access.log  main;
    autoindex off;
    index index.html index.htm;
    # Nginx处理静态资源(建议开启gzip)，LaravelS处理动态资源。
    location / {
        try_files $uri @laravels;
    }
    # 当请求PHP文件时直接响应404，防止暴露public/*.php
    #location ~* \.php$ {
    #    return 404;
    #}
    # Http和WebSocket共存，Nginx通过location区分
    # !!! WebSocket连接时路径为/ws
    # Javascript: var ws = new WebSocket("ws://laravels.com/ws");
    location =/ws {
        # proxy_connect_timeout 60s;
        # proxy_send_timeout 60s;
        # proxy_read_timeout：如果60秒内被代理的服务器没有响应数据给Nginx，那么Nginx会关闭当前连接；同时，Swoole的心跳设置也会影响连接的关闭
        # proxy_read_timeout 60s;
        proxy_http_version 1.1;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Real-PORT $remote_port;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Host $http_host;
        proxy_set_header Scheme $scheme;
        proxy_set_header Server-Protocol $server_protocol;
        proxy_set_header Server-Name $server_name;
        proxy_set_header Server-Addr $server_addr;
        proxy_set_header Server-Port $server_port;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection $connection_upgrade;
        proxy_pass http://swoole;
    }
    location @laravels {
        # proxy_connect_timeout 60s;
        # proxy_send_timeout 60s;
        # proxy_read_timeout 60s;
        proxy_http_version 1.1;
        proxy_set_header Connection "";
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Real-PORT $remote_port;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Host $http_host;
        proxy_set_header Scheme $scheme;
        proxy_set_header Server-Protocol $server_protocol;
        proxy_set_header Server-Name $server_name;
        proxy_set_header Server-Addr $server_addr;
        proxy_set_header Server-Port $server_port;
        proxy_pass http://swoole;
    }
}
```

5.心跳配置

- Swoole的心跳配置

    ```php
    // config/laravels.php
    'swoole' => [
        //...
        // 表示每60秒遍历一次，一个连接如果600秒内未向服务器发送任何数据，此连接将被强制关闭
        'heartbeat_idle_time'      => 600,
        'heartbeat_check_interval' => 60,
        //...
    ],
    ```

- Nginx读取代理服务器超时的配置

    ```nginx
    # 如果60秒内被代理的服务器没有响应数据给Nginx，那么Nginx会关闭当前连接
    proxy_read_timeout 60s;
    ```

6.在控制器中推送数据

```php
namespace App\Http\Controllers;
class TestController extends Controller
{
    public function push()
    {
        $fd = 1; // Find fd by userId from a map [userId=>fd].
        /**@var \Swoole\WebSocket\Server $swoole */
        $swoole = app('swoole');
        $success = $swoole->push($fd, 'Push data to fd#1 in Controller');
        var_dump($success);
    }
}
```

## 监听事件

### 系统事件
> 通常，你可以在这些事件中重置或销毁一些全局或静态的变量，也可以修改当前的请求和响应。

- `laravels.received_request` 将`Swoole\Http\Request`转成`Illuminate\Http\Request`后，在Laravel内核处理请求前。

    ```php
    // 修改`app/Providers/EventServiceProvider.php`, 添加下面监听代码到boot方法中
    // 如果变量$events不存在，你也可以通过Facade调用\Event::listen()。
    $events->listen('laravels.received_request', function (\Illuminate\Http\Request $req, $app) {
        $req->query->set('get_key', 'hhxsv5');// 修改querystring
        $req->request->set('post_key', 'hhxsv5'); // 修改post body
    });
    ```

- `laravels.generated_response` 在Laravel内核处理完请求后，将`Illuminate\Http\Response`转成`Swoole\Http\Response`之前(下一步将响应给客户端)。

    ```php
    // 修改`app/Providers/EventServiceProvider.php`, 添加下面监听代码到boot方法中
    // 如果变量$events不存在，你也可以通过Facade调用\Event::listen()。
    $events->listen('laravels.generated_response', function (\Illuminate\Http\Request $req, \Symfony\Component\HttpFoundation\Response $rsp, $app) {
        $rsp->headers->set('header-key', 'hhxsv5');// 修改header
    });
    ```

### 自定义的异步事件
> 此特性依赖`Swoole`的`AsyncTask`，必须先设置`config/laravels.php`的`swoole.task_worker_num`。异步事件的处理能力受Task进程数影响，需合理设置[task_worker_num](https://wiki.swoole.com/#/server/setting?id=task_worker_num)。

1.创建事件类。

```php
use Hhxsv5\LaravelS\Swoole\Task\Event;
class TestEvent extends Event
{
    protected $listeners = [
        // 监听器列表
        TestListener1::class,
        // TestListener2::class,
    ];
    private $data;
    public function __construct($data)
    {
        $this->data = $data;
    }
    public function getData()
    {
        return $this->data;
    }
}
```

2.创建监听器类。

```php
use Hhxsv5\LaravelS\Swoole\Task\Event;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Hhxsv5\LaravelS\Swoole\Task\Listener;
class TestListener1 extends Listener
{
    public function handle(Event $event)
    {
        \Log::info(__CLASS__ . ':handle start', [$event->getData()]);
        sleep(2);// 模拟一些慢速的事件处理
        // 监听器中也可以投递Task，但不支持Task的finish()回调。
        // 注意：config/laravels.php中修改配置task_ipc_mode为1或2，参考 https://wiki.swoole.com/#/server/setting?id=task_ipc_mode
        $ret = Task::deliver(new TestTask('task data'));
        var_dump($ret);
        // 此处抛出的异常会被上层捕获并记录到Swoole日志，开发者需要手动try/catch
        // return false; // 停止将事件传播到后面的监听器
    }
}
```

3.触发事件。

```php
// 实例化TestEvent并通过fire触发，此操作是异步的，触发后立即返回，由Task进程继续处理监听器中的handle逻辑
use Hhxsv5\LaravelS\Swoole\Task\Event;
$event = new TestEvent('event data');
// $event->delay(10); // 延迟10秒触发
// $event->setTries(3); // 出现异常时，累计尝试3次
$success = Event::fire($event);
var_dump($success);// 判断是否触发成功
```

## 异步的任务队列
> 此特性依赖`Swoole`的`AsyncTask`，必须先设置`config/laravels.php`的`swoole.task_worker_num`。异步任务的处理能力受Task进程数影响，需合理设置[task_worker_num](https://wiki.swoole.com/#/server/setting?id=task_worker_num)。

1.创建任务类。

```php
use Hhxsv5\LaravelS\Swoole\Task\Task;
class TestTask extends Task
{
    private $data;
    private $result;
    public function __construct($data)
    {
        $this->data = $data;
    }
    // 处理任务的逻辑，运行在Task进程中，不能投递任务
    public function handle()
    {
        \Log::info(__CLASS__ . ':handle start', [$this->data]);
        sleep(2);// 模拟一些慢速的事件处理
        // 此处抛出的异常会被上层捕获并记录到Swoole日志，开发者需要手动try/catch
        $this->result = 'the result of ' . $this->data;
    }
    // 可选的，完成事件，任务处理完后的逻辑，运行在Worker进程中，可以投递任务
    public function finish()
    {
        \Log::info(__CLASS__ . ':finish start', [$this->result]);
        Task::deliver(new TestTask2('task2')); // 投递其他任务
    }
}
```

2.投递任务。

```php
// 实例化TestTask并通过deliver投递，此操作是异步的，投递后立即返回，由Task进程继续处理TestTask中的handle逻辑
use Hhxsv5\LaravelS\Swoole\Task\Task;
$task = new TestTask('task data');
// $task->delay(3); // 延迟3秒投递任务
// $task->setTries(3); // 出现异常时，累计尝试3次
$ret = Task::deliver($task);
var_dump($ret);// 判断是否投递成功
```

## 毫秒级定时任务
> 基于[Swoole的毫秒定时器](https://wiki.swoole.com/#/timer)，封装的定时任务，取代`Linux`的`Crontab`。

1.创建定时任务类。
```php
namespace App\Jobs\Timer;
use App\Tasks\TestTask;
use Swoole\Coroutine;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Hhxsv5\LaravelS\Swoole\Timer\CronJob;
class TestCronJob extends CronJob
{
    protected $i = 0;
    // !!! 定时任务的`interval`和`isImmediate`有两种配置方式（二选一）：一是重载对应的方法，二是注册定时任务时传入参数。
    // --- 重载对应的方法来返回配置：开始
    public function interval()
    {
        return 1000;// 每1秒运行一次
    }
    public function isImmediate()
    {
        return false;// 是否立即执行第一次，false则等待间隔时间后执行第一次
    }
    // --- 重载对应的方法来返回配置：结束
    public function run()
    {
        \Log::info(__METHOD__, ['start', $this->i, microtime(true)]);
        // do something
        // sleep(1); // Swoole < 2.1
        Coroutine::sleep(1); // Swoole>=2.1 run()方法已自动创建了协程。
        $this->i++;
        \Log::info(__METHOD__, ['end', $this->i, microtime(true)]);

        if ($this->i >= 10) { // 运行10次后不再执行
            \Log::info(__METHOD__, ['stop', $this->i, microtime(true)]);
            $this->stop(); // 终止此定时任务，但restart/reload后会再次运行
            // CronJob中也可以投递Task，但不支持Task的finish()回调。
            // 注意：修改config/laravels.php，配置task_ipc_mode为1或2，参考 https://wiki.swoole.com/#/server/setting?id=task_ipc_mode
            $ret = Task::deliver(new TestTask('task data'));
            var_dump($ret);
        }
        // 此处抛出的异常会被上层捕获并记录到Swoole日志，开发者需要手动try/catch
    }
}
```

2.注册定时任务类。

```php
// 在"config/laravels.php"注册定时任务类
[
    // ...
    'timer'          => [
        'enable' => true, // 启用Timer
        'jobs'   => [ // 注册的定时任务类列表
            // 启用LaravelScheduleJob来执行`php artisan schedule:run`，每分钟一次，替代Linux Crontab
            // \Hhxsv5\LaravelS\Illuminate\LaravelScheduleJob::class,
            // 两种配置参数的方式：
            // [\App\Jobs\Timer\TestCronJob::class, [1000, true]], // 注册时传入参数
            \App\Jobs\Timer\TestCronJob::class, // 重载对应的方法来返回参数
        ],
        'max_wait_time' => 5, // Reload时最大等待时间
        // 打开全局定时器开关：当多实例部署时，确保只有一个实例运行定时任务，此功能依赖 Redis，具体请看 https://laravel.com/docs/7.x/redis
        'global_lock'     => false,
        'global_lock_key' => config('app.name', 'Laravel'),
    ],
    // ...
];
```

3.注意在构建服务器集群时，会启动多个`定时器`，要确保只启动一个定期器，避免重复执行定时任务。

4.LaravelS `v3.4.0`开始支持热重启[Reload]`定时器`进程，LaravelS 在收到`SIGUSR1`信号后会等待`max_wait_time`(默认5)秒再结束进程，然后`Manager`进程会重新拉起`定时器`进程。

5.如果你仅需要用到`分钟级`的定时任务，建议启用`Hhxsv5\LaravelS\Illuminate\LaravelScheduleJob`来替代Linux Crontab，这样就可以沿用[Laravel任务调度](https://learnku.com/docs/laravel/7.x/scheduling/7492)的编码习惯，配置`Kernel`即可。

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // runInBackground()方法会新启子进程执行任务，这是异步的，不会影响其他任务的执行时机
    $schedule->command(TestCommand::class)->runInBackground()->everyMinute();
}
```

## 修改代码后自动Reload

- 基于`inotify`，仅支持Linux。

    1.安装[inotify](http://pecl.php.net/package/inotify)扩展。

    2.开启[配置项](https://github.com/hhxsv5/laravel-s/blob/PHP-8.x/Settings-CN.md#inotify_reloadenable)。

    3.注意：`inotify`只有在`Linux`内修改文件才能收到文件变更事件，建议使用最新版Docker，[Vagrant解决方案](https://github.com/mhallin/vagrant-notify-forwarder)。

- 基于`fswatch`，支持OS X、Linux、Windows。

    1.安装[fswatch](https://github.com/emcrisostomo/fswatch)。

    2.在项目根目录下运行命令。

    ```bash
    # 监听当前目录
    ./bin/fswatch
    # 监听app目录
    ./bin/fswatch ./app
    ```

- 基于`inotifywait`，仅支持Linux。

    1.安装[inotify-tools](https://github.com/rvoicilas/inotify-tools)。

    2.在项目根目录下运行命令。

    ```bash
    # 监听当前目录
    ./bin/inotify
    # 监听app目录
    ./bin/inotify ./app
    ```

- 当以上方法都不行时，终极解决方案：配置`max_request=1,worker_num=1`，这样`Worker`进程处理完一个请求就会重启，这种方法的性能非常差，`故仅限在开发环境使用`。

## 在你的项目中使用`SwooleServer`实例

```php
/**
 * 如果启用WebSocket server，$swoole是`Swoole\WebSocket\Server`的实例，否则是是`Swoole\Http\Server`的实例
 * @var \Swoole\WebSocket\Server|\Swoole\Http\Server $swoole
 */
$swoole = app('swoole');
var_dump($swoole->stats());
$swoole->push($fd, 'Push WebSocket message');
```

## 使用`SwooleTable`

1.定义Table，支持定义多个Table。
> Swoole启动之前会创建定义的所有Table。

```php
// 在"config/laravels.php"配置
[
    // ...
    'swoole_tables'  => [
        // 场景：WebSocket中UserId与FD绑定
        'ws' => [// Key为Table名称，使用时会自动添加Table后缀，避免重名。这里定义名为wsTable的Table
            'size'   => 102400,//Table的最大行数
            'column' => [// Table的列定义
                ['name' => 'value', 'type' => \Swoole\Table::TYPE_INT, 'size' => 8],
            ],
        ],
        //...继续定义其他Table
    ],
    // ...
];
```

2.访问Table：所有的Table实例均绑定在`SwooleServer`上，通过`app('swoole')->xxxTable`访问。

```php
namespace App\Services;
use Hhxsv5\LaravelS\Swoole\WebSocketHandlerInterface;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;
class WebSocketService implements WebSocketHandlerInterface
{
    /**@var \Swoole\Table $wsTable */
    private $wsTable;
    public function __construct()
    {
        $this->wsTable = app('swoole')->wsTable;
    }
    // 场景：WebSocket中UserId与FD绑定
    public function onOpen(Server $server, Request $request)
    {
        // var_dump(app('swoole') === $server);// 同一实例
        /**
         * 获取当前登录的用户
         * 此特性要求建立WebSocket连接的路径要经过Authenticate之类的中间件。
         * 例如：
         * 浏览器端：var ws = new WebSocket("ws://127.0.0.1:5200/ws");
         * 那么Laravel中/ws路由就需要加上类似Authenticate的中间件。
         * Route::get('/ws', function () {
         *     // 响应状态码200的任意内容
         *     return 'websocket';
         * })->middleware(['auth']);
         */
        // $user = Auth::user();
        // $userId = $user ? $user->id : 0; // 0 表示未登录的访客用户
        $userId = mt_rand(1000, 10000);
        // if (!$userId) {
        //     // 未登录用户直接断开连接
        //     $server->disconnect($request->fd);
        //     return;
        // }
        $this->wsTable->set('uid:' . $userId, ['value' => $request->fd]);// 绑定uid到fd的映射
        $this->wsTable->set('fd:' . $request->fd, ['value' => $userId]);// 绑定fd到uid的映射
        $server->push($request->fd, "Welcome to LaravelS #{$request->fd}");
    }
    public function onMessage(Server $server, Frame $frame)
    {
        // 广播
        foreach ($this->wsTable as $key => $row) {
            if (strpos($key, 'uid:') === 0 && $server->isEstablished($row['value'])) {
                $content = sprintf('Broadcast: new message "%s" from #%d', $frame->data, $frame->fd);
                $server->push($row['value'], $content);
            }
        }
    }
    public function onClose(Server $server, $fd, $reactorId)
    {
        $uid = $this->wsTable->get('fd:' . $fd);
        if ($uid !== false) {
            $this->wsTable->del('uid:' . $uid['value']); // 解绑uid映射
        }
        $this->wsTable->del('fd:' . $fd);// 解绑fd映射
        $server->push($fd, "Goodbye #{$fd}");
    }
}
```

## 多端口混合协议

> 更多的信息，请参考[Swoole增加监听的端口](https://wiki.swoole.com/#/server/methods?id=addlistener)与[多端口混合协议](https://wiki.swoole.com/#/server/port)

为了使我们的主服务器能支持除`HTTP`和`WebSocket`外的更多协议，我们引入了`Swoole`的`多端口混合协议`特性，在LaravelS中称为`Socket`。现在，可以很方便地在Laravel上构建`TCP/UDP`应用。

1. 创建`Socket`处理类，继承`Hhxsv5\LaravelS\Swoole\Socket\{TcpSocket|UdpSocket|Http|WebSocket}`

    ```php
    namespace App\Sockets;
    use Hhxsv5\LaravelS\Swoole\Socket\TcpSocket;
    use Swoole\Server;
    class TestTcpSocket extends TcpSocket
    {
        public function onConnect(Server $server, $fd, $reactorId)
        {
            \Log::info('New TCP connection', [$fd]);
            $server->send($fd, 'Welcome to LaravelS.');
        }
        public function onReceive(Server $server, $fd, $reactorId, $data)
        {
            \Log::info('Received data', [$fd, $data]);
            $server->send($fd, 'LaravelS: ' . $data);
            if ($data === "quit\r\n") {
                $server->send($fd, 'LaravelS: bye' . PHP_EOL);
                $server->close($fd);
            }
        }
        public function onClose(Server $server, $fd, $reactorId)
        {
            \Log::info('Close TCP connection', [$fd]);
            $server->send($fd, 'Goodbye');
        }
    }
    ```

    这些连接和主服务器上的HTTP/WebSocket连接共享`Worker`进程，因此可以在这些事件回调中使用LaravelS提供的`异步任务投递`、`SwooleTable`、Laravel提供的组件如`DB`、`Eloquent`等。同时，如果需要使用该协议端口的`Swoole\Server\Port`对象，只需要像如下代码一样访问`Socket`类的成员`swoolePort`即可。

    ```php
    public function onReceive(Server $server, $fd, $reactorId, $data)
    {
        $port = $this->swoolePort; // 获得`Swoole\Server\Port`对象
    }
    ```

    ```php
    namespace App\Http\Controllers;
    class TestController extends Controller
    {
        public function test()
        {
            /**@var \Swoole\Http\Server|\Swoole\WebSocket\Server $swoole */
            $swoole = app('swoole');
            // $swoole->ports：遍历所有Port对象，https://wiki.swoole.com/#/server/properties?id=ports
            $port = $swoole->ports[1]; // 获得`Swoole\Server\Port`对象，$port[0]是主服务器的端口
            foreach ($port->connections as $fd) { // 遍历所有连接
                // $swoole->send($fd, 'Send tcp message');
                // if($swoole->isEstablished($fd)) {
                //     $swoole->push($fd, 'Send websocket message');
                // }
            }
        }
    }
    ```

2. 注册套接字。

    ```php
    // 修改文件 config/laravels.php
    // ...
    'sockets' => [
        [
            'host'     => '127.0.0.1',
            'port'     => 5291,
            'type'     => SWOOLE_SOCK_TCP,// 支持的嵌套字类型：https://wiki.swoole.com/#/consts?id=socket-%e7%b1%bb%e5%9e%8b
            'settings' => [// Swoole可用的配置项：https://wiki.swoole.com/#/server/port?id=%e5%8f%af%e9%80%89%e5%8f%82%e6%95%b0
                'open_eof_check' => true,
                'package_eof'    => "\r\n",
            ],
            'handler'  => \App\Sockets\TestTcpSocket::class,
            'enable'   => true, // 是否启用，默认为true
        ],
    ],
    ```

    关于心跳配置，只能设置在`主服务器`上，不能配置在`套接字`上，但`套接字`会继承`主服务器`的心跳配置。

    对于TCP协议，`dispatch_mode`选项设为`1/3`时，底层会屏蔽`onConnect`/`onClose`事件，原因是这两种模式下无法保证`onConnect`/`onClose`/`onReceive`的顺序。如果需要用到这两个事件，请将`dispatch_mode`改为`2/4/5`，[参考](https://wiki.swoole.com/#/server/setting?id=dispatch_mode)。

    ```php
    'swoole' => [
        //...
        'dispatch_mode' => 2,
        //...
    ];
    ```

3. 测试。

- TCP：`telnet 127.0.0.1 5291`

- UDP：Linux下 `echo "Hello LaravelS" > /dev/udp/127.0.0.1/5292`

4. 其他协议的注册示例。

    - UDP
    ```php
    'sockets' => [
        [
            'host'     => '0.0.0.0',
            'port'     => 5292,
            'type'     => SWOOLE_SOCK_UDP,
            'settings' => [
                'open_eof_check' => true,
                'package_eof'    => "\r\n",
            ],
            'handler'  => \App\Sockets\TestUdpSocket::class,
        ],
    ],
    ```

    - Http
    ```php
    'sockets' => [
        [
            'host'     => '0.0.0.0',
            'port'     => 5293,
            'type'     => SWOOLE_SOCK_TCP,
            'settings' => [
                'open_http_protocol' => true,
            ],
            'handler'  => \App\Sockets\TestHttp::class,
        ],
    ],
    ```

    - WebSocket：主服务器必须`开启WebSocket`，即需要将`websocket.enable`置为`true`。
    ```php
    'sockets' => [
        [
            'host'     => '0.0.0.0',
            'port'     => 5294,
            'type'     => SWOOLE_SOCK_TCP,
            'settings' => [
                'open_http_protocol'      => true,
                'open_websocket_protocol' => true,
            ],
            'handler'  => \App\Sockets\TestWebSocket::class,
        ],
    ],
    ```


## 协程

> [Swoole原始文档](https://wiki.swoole.com/#/start/coroutine)

- 警告：协程下代码执行顺序是乱序的，请求级的数据应该以协程ID隔离，但Laravel/Lumen中存在很多单例、静态属性，不同请求间的数据会相互影响，这是`不安全`的。比如数据库连接就是单例，同一个数据库连接共享同一个PDO资源，这在同步阻塞模式下是没问题的，但在异步协程下是不行的，每次查询需要创建不同的连接，维护不同的IO状态，这就需要用到连接池。

- `不要`使用协程，仅`自定义进程`中可使用协程。

## 自定义进程

> 支持开发者创建一些特殊的工作进程，用于监控、上报或者其他特殊的任务，参考[addProcess](https://wiki.swoole.com/#/server/methods?id=addprocess)。

1. 创建Proccess类，实现CustomProcessInterface接口。

    ```php
    namespace App\Processes;
    use App\Tasks\TestTask;
    use Hhxsv5\LaravelS\Swoole\Process\CustomProcessInterface;
    use Hhxsv5\LaravelS\Swoole\Task\Task;
    use Swoole\Coroutine;
    use Swoole\Http\Server;
    use Swoole\Process;
    class TestProcess implements CustomProcessInterface
    {
        /**
         * @var bool 退出标记，用于Reload更新
         */
        private static $quit = false;

        public static function callback(Server $swoole, Process $process)
        {
            // 进程运行的代码，不能退出，一旦退出Manager进程会自动再次创建该进程。
            while (!self::$quit) {
                \Log::info('Test process: running');
                // sleep(1); // Swoole < 2.1
                Coroutine::sleep(1); // Swoole>=2.1 已自动为callback()方法创建了协程并启用了协程Runtime，注意所使用的组件与协程兼容性，不兼容时可仅开启部分协程，如：\Swoole\Runtime::enableCoroutine(SWOOLE_HOOK_TCP | SWOOLE_HOOK_SLEEP | SWOOLE_HOOK_FILE);
                // 自定义进程中也可以投递Task，但不支持Task的finish()回调。
                // 注意：修改config/laravels.php，配置task_ipc_mode为1或2，参考 https://wiki.swoole.com/#/server/setting?id=task_ipc_mode
                $ret = Task::deliver(new TestTask('task data'));
                var_dump($ret);
                // 上层会捕获callback中抛出的异常，并记录到Swoole日志，然后此进程会退出，3秒后Manager进程会重新创建进程，所以需要开发者自行try/catch捕获异常，避免频繁创建进程。
                // throw new \Exception('an exception');
            }
        }
        // 要求：LaravelS >= v3.4.0 并且 callback() 必须是异步非阻塞程序。
        public static function onReload(Server $swoole, Process $process)
        {
            // Stop the process...
            // Then end process
            \Log::info('Test process: reloading');
            self::$quit = true;
            // $process->exit(0); // 强制退出进程
        }
        // 要求：LaravelS >= v3.7.4 并且 callback() 必须是异步非阻塞程序。
        public static function onStop(Server $swoole, Process $process)
        {
            // Stop the process...
            // Then end process
            \Log::info('Test process: stopping');
            self::$quit = true;
            // $process->exit(0); // 强制退出进程
        }
    }
    ```

2. 注册TestProcess。

    ```php
    // 修改文件 config/laravels.php
    // ...
    'processes' => [
        'test' => [ // Key为进程名
            'class'    => \App\Processes\TestProcess::class,
            'redirect' => false, // 是否重定向输入输出
            'pipe'     => 0,     // 管道类型：0不创建管道，1创建SOCK_STREAM类型管道，2创建SOCK_DGRAM类型管道
            'enable'   => true,  // 是否启用，默认true
            //'num'    => 3,  // 创建多个进程实例，默认为1
            //'queue'    => [ // 启用消息队列作为进程间通信，配置空数组表示使用默认参数
            //    'msg_key'  => 0,    // 消息队列的KEY，默认会使用ftok(__FILE__, 1)
            //    'mode'     => 2,    // 通信模式，默认为2，表示争抢模式
            //    'capacity' => 8192, // 单个消息长度，长度受限于操作系统内核参数的限制，默认为8192，最大不超过65536
            //],
            //'restart_interval' => 5, // 进程异常退出后需等待多少秒再重启，默认5秒
        ],
    ],
    ```

3. 注意：callback()方法不能退出，如果退出，Manager进程将会重新创建进程。

4. 示例：向自定义进程中写数据。

    ```php
    // config/laravels.php
    'processes' => [
        'test' => [
            'class'    => \App\Processes\TestProcess::class,
            'redirect' => false,
            'pipe'     => 1,
        ],
    ],
    ```

    ```php
    // app/Processes/TestProcess.php
    public static function callback(Server $swoole, Process $process)
    {
        while ($data = $process->read()) {
            \Log::info('TestProcess: read data', [$data]);
            $process->write('TestProcess: ' . $data);
        }
    }
    ```

    ```php
    // app/Http/Controllers/TestController.php
    public function testProcessWrite()
    {
        /**@var \Swoole\Process[] $process */
        $customProcesses = \Hhxsv5\LaravelS\LaravelS::getCustomProcesses();
        $process = $customProcesses['test'];
        $process->write('TestController: write data' . time());
        var_dump($process->read());
    }
    ```

## 常用组件

### Apollo
> 启动`LaravelS`时会获取`Apollo`配置并写入到`.env`文件，同时会启动自定义进程`apollo`用于监听配置变更，当配置发生变更时自动`reload`。

1. 启用Apollo组件：启动参数加上`--enable-apollo`以及Apollo的配置参数。
    
    ```bash
    php bin/laravels start --enable-apollo --apollo-server=http://127.0.0.1:8080 --apollo-app-id=LARAVEL-S-TEST
    ```

2. 配置热更新（可选的）。

    ```php
    // 修改文件 config/laravels.php
    'processes' => Hhxsv5\LaravelS\Components\Apollo\Process::getDefinition(),
    ```

    ```php
    // 当存在其他自定义进程配置时
    'processes' => [
        'test' => [
            'class'    => \App\Processes\TestProcess::class,
            'redirect' => false,
            'pipe'     => 1,
        ],
        // ...
    ] + Hhxsv5\LaravelS\Components\Apollo\Process::getDefinition(),
    ```

3. 可用的参数列表。

| 参数名 | 描述 | 默认值 | 示例 |
| -------- | -------- | -------- | -------- |
| apollo-server | Apollo服务器URL | - | --apollo-server=http://127.0.0.1:8080 |
| apollo-app-id | Apollo应用ID | - | --apollo-app-id=LARAVEL-S-TEST |
| apollo-namespaces | APP所属的命名空间，可指定多个 | application | --apollo-namespaces=application --apollo-namespaces=env |
| apollo-cluster | APP所属的集群 | default | --apollo-cluster=default |
| apollo-client-ip | 当前实例的IP，还可用于灰度发布 | 本机内网IP | --apollo-client-ip=10.2.1.83 |
| apollo-pull-timeout | 拉取配置时的超时时间（秒） | 5 | --apollo-pull-timeout=5 |
| apollo-backup-old-env | 更新配置文件`.env`时是否备份老的配置文件 | false | --apollo-backup-old-env |

### Prometheus
> 支持Prometheus监控与告警，Grafana可视化查看监控指标。请参考[Docker Compose](https://github.com/hhxsv5/docker)完成Prometheus与Grafana的环境搭建。

1. 依赖[APCu >= 5.0.0](https://pecl.php.net/package/apcu)扩展，请先安装它 `pecl install apcu`。

2. 拷贝配置文件`prometheus.php`到你的工程`config`目录。视情况修改配置。
    ```bash
    # 项目根目录下执行命令
    cp vendor/hhxsv5/laravel-s/config/prometheus.php config/
    ```
    如果是`Lumen`工程，还需要在`bootstrap/app.php`中手动加载配置`$app->configure('prometheus');`。

3. 配置`全局`中间件：`Hhxsv5\LaravelS\Components\Prometheus\RequestMiddleware::class`。为了尽可能精确地统计请求耗时，`RequestMiddleware`必须作为`第一个`全局中间件，需要放在其他中间件的前面。

4. 注册 ServiceProvider：`Hhxsv5\LaravelS\Components\Prometheus\ServiceProvider::class`。

5. 在`config/laravels.php`中配置 CollectorProcess 进程，用于定时采集 Swoole Worker/Task/Timer 进程的指标。
    ```php
    'processes' => Hhxsv5\LaravelS\Components\Prometheus\CollectorProcess::getDefinition(),
    ```

6. 创建路由，输出监控指标数据。
    ```php
    use Hhxsv5\LaravelS\Components\Prometheus\Exporter;

    Route::get('/actuator/prometheus', function () {
        $result = app(Exporter::class)->render();
        return response($result, 200, ['Content-Type' => Exporter::REDNER_MIME_TYPE]);
    });
    ```

7. 完成Prometheus的配置，启动Prometheus。
    ```yml
    global:
      scrape_interval: 5s
      scrape_timeout: 5s
      evaluation_interval: 30s
    scrape_configs:
    - job_name: laravel-s-test
      honor_timestamps: true
      metrics_path: /actuator/prometheus
      scheme: http
      follow_redirects: true
      static_configs:
      - targets:
        - 127.0.0.1:5200 # The ip and port of the monitored service
    # Dynamically discovered using one of the supported service-discovery mechanisms
    # https://prometheus.io/docs/prometheus/latest/configuration/configuration/#scrape_config
    # - job_name: laravels-eureka
    #   honor_timestamps: true
    #   scrape_interval: 5s
    #   metrics_path: /actuator/prometheus
    #   scheme: http
    #   follow_redirects: true
      # eureka_sd_configs:
      # - server: http://127.0.0.1:8080/eureka
      #   follow_redirects: true
      #   refresh_interval: 5s
    ```

8. 启动Grafana，然后导入[panel json](https://github.com/hhxsv5/laravel-s/tree/PHP-8.x/grafana-dashboard.json)。

<img src="https://raw.githubusercontent.com/hhxsv5/laravel-s/PHP-8.x/grafana-dashboard.png" height="800px" alt="Grafana Dashboard">

## 其他特性

### 配置Swoole事件

支持的事件列表：

| 事件 | 需实现的接口 | 发生时机 |
| -------- | -------- | -------- |
| ServerStart | Hhxsv5\LaravelS\Swoole\Events\ServerStartInterface | 发生在Master进程启动时，`此事件中不应处理复杂的业务逻辑，只能做一些初始化的简单工作` |
| ServerStop | Hhxsv5\LaravelS\Swoole\Events\ServerStopInterface | 发生在Server正常退出时，`此事件中不能使用异步或协程相关的API` |
| WorkerStart | Hhxsv5\LaravelS\Swoole\Events\WorkerStartInterface | 发生在Worker/Task进程启动完成后 |
| WorkerStop | Hhxsv5\LaravelS\Swoole\Events\WorkerStopInterface | 发生在Worker/Task进程正常退出后 |
| WorkerError | Hhxsv5\LaravelS\Swoole\Events\WorkerErrorInterface | 发生在Worker/Task进程发生异常或致命错误时 |

1.创建事件处理类，实现相应的接口。
```php
namespace App\Events;
use Hhxsv5\LaravelS\Swoole\Events\ServerStartInterface;
use Swoole\Atomic;
use Swoole\Http\Server;
class ServerStartEvent implements ServerStartInterface
{
    public function __construct()
    {
    }
    public function handle(Server $server)
    {
        // 初始化一个全局计数器(跨进程的可用)
        $server->atomicCount = new Atomic(2233);

        // 控制器中调用：app('swoole')->atomicCount->get();
    }
}
```

```php
namespace App\Events;
use Hhxsv5\LaravelS\Swoole\Events\WorkerStartInterface;
use Swoole\Http\Server;
class WorkerStartEvent implements WorkerStartInterface
{
    public function __construct()
    {
    }
    public function handle(Server $server, $workerId)
    {
        // 初始化一个数据库连接池对象
        // DatabaseConnectionPool::init();
    }
}
```

2.配置。
```php
// 修改文件 config/laravels.php
'event_handlers' => [
    'ServerStart' => [\App\Events\ServerStartEvent::class], // 按数组顺序触发事件
    'WorkerStart' => [\App\Events\WorkerStartEvent::class],
],
```

### Serverless

#### 阿里云函数计算
> [函数计算官方文档](https://help.aliyun.com/product/50980.html)。

1.修改`bootstrap/app.php`，设置storage目录。因为项目目录只读，`/tmp`目录才可读写。

```php
$app->useStoragePath(env('APP_STORAGE_PATH', '/tmp/storage'));
```

2.创建Shell脚本`laravels_bootstrap`，并赋予`可执行权限`。

```bash
#!/usr/bin/env bash
set +e

# 创建storage相关目录
mkdir -p /tmp/storage/app/public
mkdir -p /tmp/storage/framework/cache
mkdir -p /tmp/storage/framework/sessions
mkdir -p /tmp/storage/framework/testing
mkdir -p /tmp/storage/framework/views
mkdir -p /tmp/storage/logs

# 设置环境变量APP_STORAGE_PATH，请确保与.env的APP_STORAGE_PATH一样
export APP_STORAGE_PATH=/tmp/storage

# Start LaravelS
php bin/laravels start
```

3.配置`template.xml`。

```xml
ROSTemplateFormatVersion: '2015-09-01'
Transform: 'Aliyun::Serverless-2018-04-03'
Resources:
  laravel-s-demo:
    Type: 'Aliyun::Serverless::Service'
    Properties:
      Description: 'LaravelS Demo for Serverless'
    fc-laravel-s:
      Type: 'Aliyun::Serverless::Function'
      Properties:
        Handler: laravels.handler
        Runtime: custom
        MemorySize: 512
        Timeout: 30
        CodeUri: ./
        InstanceConcurrency: 10
        EnvironmentVariables:
          BOOTSTRAP_FILE: laravels_bootstrap

```

## 注意事项

### 单例问题

- 传统FPM下，单例模式的对象的生命周期仅在每次请求中，请求开始=>实例化单例=>请求结束后=>单例对象资源回收。

- Swoole Server下，所有单例对象会常驻于内存，这个时候单例对象的生命周期与FPM不同，请求开始=>实例化单例=>请求结束=>单例对象依旧保留，需要开发者自己维护单例的状态。

- 常见的解决方案：

    1. 写一个`XxxCleaner`清理器类来清理单例对象状态，此类需实现接口`Hhxsv5\LaravelS\Illuminate\Cleaners\CleanerInterface`，然后注册到`laravels.php`的`cleaners`中。
    
    2. 用一个`中间件`来`重置`单例对象的状态。

    3. 如果是以`ServiceProvider`注册的单例对象，可添加该`ServiceProvider`到`laravels.php`的`register_providers`中，这样每次请求会重新注册该`ServiceProvider`，重新实例化单例对象，[参考](https://github.com/hhxsv5/laravel-s/blob/PHP-8.x/Settings-CN.md#register_providers)。

### 清理器
> [设置清理器](https://github.com/hhxsv5/laravel-s/blob/PHP-8.x/Settings-CN.md#cleaners)。

### 常见问题
> [常见问题](https://github.com/hhxsv5/laravel-s/blob/PHP-8.x/KnownIssues-CN.md)：一揽子的已知问题和解决方案。

### 调试方式

- 记录日志；如想要在控制台输出，可使用`stderr`，Log::channel('stderr')->debug('debug message')。

- [Laravel Dump Server](https://github.com/beyondcode/laravel-dump-server)（Laravel 5.7已默认集成）。

### 读取请求
应通过`Illuminate\Http\Request`对象来读取请求信息，$_ENV是可读取的，$_SERVER是部分可读的，`不能使用`$_GET、$_POST、$_FILES、$_COOKIE、$_REQUEST、$_SESSION、$GLOBALS。

```php
public function form(\Illuminate\Http\Request $request)
{
    $name = $request->input('name');
    $all = $request->all();
    $sessionId = $request->cookie('sessionId');
    $photo = $request->file('photo');
    // 调用getContent()来获取原始的POST body，而不能用file_get_contents('php://input')
    $rawContent = $request->getContent();
    //...
}
```

### 输出响应
推荐通过返回`Illuminate\Http\Response`对象来响应请求，兼容echo、vardump()、print_r()，`不能使用`函数 dd()、exit()、die()、header()、setcookie()、http_response_code()。

```php
public function json()
{
    return response()->json(['time' => time()])->header('header1', 'value1')->withCookie('c1', 'v1');
}
```

### 持久连接
`单例的连接`将被常驻内存，建议开启`持久连接`，获得更好的性能。
1. 数据库连接，连接断开后会自动重连

```php
// config/database.php
'connections' => [
    'my_conn' => [
        'driver'    => 'mysql',
        'host'      => env('DB_MY_CONN_HOST', 'localhost'),
        'port'      => env('DB_MY_CONN_PORT', 3306),
        'database'  => env('DB_MY_CONN_DATABASE', 'forge'),
        'username'  => env('DB_MY_CONN_USERNAME', 'forge'),
        'password'  => env('DB_MY_CONN_PASSWORD', ''),
        'charset'   => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix'    => '',
        'strict'    => false,
        'options'   => [
            // 开启持久连接
            \PDO::ATTR_PERSISTENT => true,
        ],
    ],
],
```

2. Redis连接，连接断开后`不会立即`自动重连，会抛出一个关于连接断开的异常，下次会自动重连。需确保每次操作Redis前正确的`SELECT DB`。

```php
// config/database.php
'redis' => [
    'client' => env('REDIS_CLIENT', 'phpredis'), // 推荐使用phpredis，以获得更好的性能
    'default' => [
        'host'       => env('REDIS_HOST', 'localhost'),
        'password'   => env('REDIS_PASSWORD', null),
        'port'       => env('REDIS_PORT', 6379),
        'database'   => 0,
        'persistent' => true, // 开启持久连接
    ],
],
```

### 关于内存泄露

- 避免使用全局变量，如一定要，请手动清理或重置。

- 无限追加元素到全局变量、静态变量、单例，将导致内存溢出。

    ```php
    class Test
    {
        public static $array = [];
        public static $string = '';
    }

    // 某控制器
    public function test(Request $req)
    {
        // 内存溢出
        Test::$array[] = $req->input('param1');
        Test::$string .= $req->input('param2');
    }
    ```

- 内存泄露的检测方法

    1. 修改`config/laravels.php`：`worker_num=1, max_request=1000000`，测试完成后记得改回去；

    2. 增加路由`/debug-memory-leak`，不设置任何`路由中间件`，用于观察`Worker`进程的内存变化情况；

    ```php
    Route::get('/debug-memory-leak', function () {
        global $previous;
        $current = memory_get_usage();
        $stats = [
            'prev_mem' => $previous,
            'curr_mem' => $current,
            'diff_mem' => $current - $previous,
        ];
        $previous = $current;
        return $stats;
    });
    ```

    3. 启动`LaravelS`，请求`/debug-memory-leak`，直到`diff_mem`小于或等于零；如果`diff_mem`一直大于零，说明`全局中间件`或`Laravel框架`可能存在内存泄露；
    
    4. 完成`步骤3`后，`交替`请求业务路由与`/debug-memory-leak`（建议使用`ab`/`wrk`对业务路由进行大量的请求），刚开始出现的内存增涨是正常现象。业务路由经过大量请求后，如果`diff_mem`一直大于零，并且`curr_mem`持续增大，则大概率存在内存泄露；如果`curr_mem`始终在一定范围内变化，没有持续变大，则大概率不存在内存泄露。

    5. 如果始终没法解决，[max_request](https://wiki.swoole.com/#/server/setting?id=max_request)是最后兜底的方案。


### Linux内核参数调整
> [Linux内核参数调整](https://wiki.swoole.com/#/other/sysctl?id=%e5%86%85%e6%a0%b8%e5%8f%82%e6%95%b0%e8%b0%83%e6%95%b4)

### 压力测试
> [压力测试](https://wiki.swoole.com/wiki/page/62.html)

## 用户与案例

- `特别赞助商` [果酱社区](https://guojiang.club/?utm_source=laravels&utm_campaign=sponsor)：果酱社区是一个专业的会员制社区，这里提供高质量的技术资料，
专注程序员成长，创业孵化，提供优秀且稳定的开源产品。
    
    <img src="https://user-images.githubusercontent.com/7278743/72538652-19bad000-38b9-11ea-844a-8f66a2c76185.jpeg" height="300px" alt="果酱社区">

- [KuCoin](https://www.kucoin.com/r/rf/QBSADJ41)：TOP5的加密货币交易所。

- [医联](https://www.medlinker.com/)：WEB站、M站、APP、小程序的账户体系服务。
    
    <img src="https://user-images.githubusercontent.com/7278743/46649457-af05e980-cbcb-11e8-94a1-b13d743d33fd.png" height="300px" alt="医联">

- [ITOK在线客服平台](http://demo.topitsm.com)：用户IT工单的处理跟踪及在线实时沟通。
    
    <img src="https://user-images.githubusercontent.com/7278743/46649548-10c65380-cbcc-11e8-81e6-f4a8dca2eb2c.png" height="300px" alt="ITOK在线客服平台">

- [盟呱呱](http://mgg.yamecent.com)
    
    <img src="https://user-images.githubusercontent.com/7278743/46648932-b3310780-cbc9-11e8-971e-ca26e3378507.png" height="300px" alt="盟呱呱">

- [WookTeam](https://github.com/kuaifan/wookteam)：WookTeam是一款轻量级的在线团队协作工具，提供各类文档工具、在线思维导图、在线流程图、项目管理、任务分发，知识库管理等工具。

- 微信公众号-广州塔：活动、商城
    
    <img src="https://user-images.githubusercontent.com/7278743/46649832-1a9c8680-cbcd-11e8-902e-978fa644f4d9.png" height="300px" alt="广州塔">

- 企鹅游戏盒子、明星新势力、以及小程序广告服务
    
    <img src="https://user-images.githubusercontent.com/7278743/46649296-2c7d2a00-cbcb-11e8-94d3-bc12fc9566d6.jpg" height="300px" alt="企鹅游戏盒子">

- 小程序-修机匠手机上门维修服务：手机维修服务，提供上门服务，支持在线维修。
    
    <img src="https://user-images.githubusercontent.com/7278743/46941544-eda11580-d09d-11e8-8c3a-16c567655277.png" height="300px" alt="修机匠手机上门维修服务">

- 亿健APP

## 其他选择

- [swooletw/laravel-swoole](https://github.com/swooletw/laravel-swoole)

## 赞助
> 您的支持是我们坚持的最大动力。

<img src="https://raw.githubusercontent.com/hhxsv5/laravel-s/PHP-8.x/sponsor.png" height="300px" alt="赞助">

### 感谢

| 支持者 | 金额(元) |
| --- | --- |
| *思勇 | 18.88 |
| *德国 | 18.88 |
| 魂之挽歌 | 100 |
| 小南瓜 | 10.01 |
| *丁智 | 16.66 |
| 匿名 | 20 |
| 匿名 | 20 |
| *洋 Blues | 18.88 |
| *钧泽 Panda | 10.24 |
| *翔 海贼王路飞 | 12 |
| *跃 Axiong | 10 |
| 落伽 | 10 |
| 很胖的胖子 | 15 |
| 霹格软件 | 18.88 |
| Bygones | 18.88 |
| *春 Flymoo | 100 |
| 异乡人 | 20 |
| only丶妳 | 100 |
| 月殇 | 18.88 |
| Shmily | 20 |
| *俊 | 20 |
| *哲 | 20 |
| Alex | 20 |
| X | 20 |
| *洋 | 20 |
| *洋 | 20 |
| *强 | 50 |
| Anthony | 18.88 |
| *官龙 | 100 |
| 0o飞舞o0木木 *科 | 288 |
| *勇 | 66.66 |
| 果酱社区 | 1076 |
| *乾 | 18.88 |
| *奥 | 8.88 |
| 双木 | 20 |

## Star历史

[![Star History Chart](https://star-history.dera.page/svg?repos=hhxsv5/laravel-s&type=date&legend=top-left)](https://star-history.dera.page/#hhxsv5/laravel-s&type=date&legend=top-left)

## License

[MIT](https://github.com/hhxsv5/laravel-s/blob/PHP-8.x/LICENSE)
