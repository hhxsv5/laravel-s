```
 _                               _  _____ 
| |                             | |/ ____|
| |     __ _ _ __ __ ___   _____| | (___  
| |    / _` | '__/ _` \ \ / / _ \ |\___ \ 
| |___| (_| | | | (_| |\ V /  __/ |____) |
|______\__,_|_|  \__,_| \_/ \___|_|_____/ 
                                           
```
> 🚀`LaravelS`是一个胶水项目，用于快速集成`Swoole`到`Laravel`或`Lumen`，然后赋予它们更好的性能、更多可能性。

[![Latest Stable Version](https://poser.pugx.org/hhxsv5/laravel-s/v/stable.svg)](https://packagist.org/packages/hhxsv5/laravel-s)
[![Latest Unstable Version](https://poser.pugx.org/hhxsv5/laravel-s/v/unstable.svg)](https://packagist.org/packages/hhxsv5/laravel-s)
[![Total Downloads](https://poser.pugx.org/hhxsv5/laravel-s/downloads.svg)](https://packagist.org/packages/hhxsv5/laravel-s)
[![License](https://poser.pugx.org/hhxsv5/laravel-s/license.svg)](https://github.com/hhxsv5/laravel-s/blob/master/LICENSE)
[![Build Status](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/badges/build.png?b=master)](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/build-status/master)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)
<!-- [![Code Coverage](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/?branch=master) -->

**[English Documentation](https://github.com/hhxsv5/laravel-s/blob/master/README.md)**

**QQ交流群**
- 群1：`698480528`(已满) [![点击加群](https://pub.idqqimg.com/wpa/images/group.png "点击加群")](//shang.qq.com/wpa/qunwpa?idkey=f949191c8f413a3ecc5fbce661e57d379740ba92172bd50b02d23a5ab36cc7d6)
- 群2：`62075835` [![点击加群](https://pub.idqqimg.com/wpa/images/group.png "点击加群")](//shang.qq.com/wpa/qunwpa?idkey=5230f8da0693a812811e21e19d5823ee802ee5d24def177663f42a32a9060e97)

Table of Contents
=================

* [特性](#特性)
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
* [在你的项目中使用swoole_server实例](#在你的项目中使用swoole_server实例)
* [使用swoole_table](#使用swoole_table)
* [多端口混合协议](#多端口混合协议)
* [协程](#协程)
* [自定义进程](#自定义进程)
* [注意事项](#注意事项)
* [用户与案例](#用户与案例)
* [待办事项](#待办事项)
* [其他选择](#其他选择)
* [打赏](#打赏)
    * [感谢](#感谢)
* [License](#license)

## 特性

- 内置Http/[WebSocket](https://github.com/hhxsv5/laravel-s/blob/master/README-CN.md#%E5%90%AF%E7%94%A8websocket%E6%9C%8D%E5%8A%A1%E5%99%A8)服务器

- [多端口混合协议](https://github.com/hhxsv5/laravel-s/blob/master/README-CN.md#%E5%A4%9A%E7%AB%AF%E5%8F%A3%E6%B7%B7%E5%90%88%E5%8D%8F%E8%AE%AE)

- [协程](https://github.com/hhxsv5/laravel-s/blob/master/README-CN.md#%E5%8D%8F%E7%A8%8B)

- [自定义进程](https://github.com/hhxsv5/laravel-s/blob/master/README-CN.md#%E8%87%AA%E5%AE%9A%E4%B9%89%E8%BF%9B%E7%A8%8B)

- 常驻内存

- [异步的事件监听](https://github.com/hhxsv5/laravel-s/blob/master/README-CN.md#%E8%87%AA%E5%AE%9A%E4%B9%89%E7%9A%84%E5%BC%82%E6%AD%A5%E4%BA%8B%E4%BB%B6)

- [异步的任务队列](https://github.com/hhxsv5/laravel-s/blob/master/README-CN.md#%E5%BC%82%E6%AD%A5%E7%9A%84%E4%BB%BB%E5%8A%A1%E9%98%9F%E5%88%97)

- [毫秒级定时任务](https://github.com/hhxsv5/laravel-s/blob/master/README-CN.md#%E6%AF%AB%E7%A7%92%E7%BA%A7%E5%AE%9A%E6%97%B6%E4%BB%BB%E5%8A%A1)

- 平滑Reload

- [修改代码后自动Reload](https://github.com/hhxsv5/laravel-s/blob/master/README-CN.md#%E4%BF%AE%E6%94%B9%E4%BB%A3%E7%A0%81%E5%90%8E%E8%87%AA%E5%8A%A8reload)

- 同时支持Laravel与Lumen，兼容主流版本

- 简单，开箱即用

## 要求

| 依赖 | 说明 |
| -------- | -------- |
| [PHP](https://secure.php.net/manual/zh/install.php) | `>= 5.5.9` `推荐PHP7+` |
| [Swoole](https://www.swoole.com/) | `>= 1.7.19` `从2.0.12开始不再支持PHP5` `推荐4.2.3+` |
| [Laravel](https://laravel.com/)/[Lumen](https://lumen.laravel.com/) | `>= 5.1` `推荐5.6+` |

## 安装

1.通过[Composer](https://getcomposer.org/)安装([packagist](https://packagist.org/packages/hhxsv5/laravel-s))。有可能找不到`3.0`版本，解决方案移步[#81](https://github.com/hhxsv5/laravel-s/issues/81)。

```bash
composer require "hhxsv5/laravel-s:~3.0" -vvv
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

3.发布配置文件。
> *每次升级LaravelS后，建议重新发布一次配置文件*
```bash
php artisan laravels publish
```

`使用Lumen时的特别说明`: 你不需要手动加载配置`laravels.php`，LaravelS底层已自动加载。
```php
// 不必手动加载，但加载了也不会有问题
$app->configure('laravels');
```

4.修改配置`config/laravels.php`：监听的IP、端口等，请参考[配置项](https://github.com/hhxsv5/laravel-s/blob/master/Settings-CN.md)。

## 运行
> `php artisan laravels {start|stop|restart|reload|publish}`

`在运行之前，请先仔细阅读：`[注意事项](https://github.com/hhxsv5/laravel-s/blob/master/README-CN.md#%E6%B3%A8%E6%84%8F%E4%BA%8B%E9%A1%B9)。

| 命令 | 说明 |
| --------- | --------- |
| `start` | 启动LaravelS，展示已启动的进程列表 "*ps -ef&#124;grep laravels*"。添加选项`-d`或`--daemonize`以守护进程的方式运行，此选项将覆盖`laravels.php`中`swoole.daemonize`设置 |
| `stop` | 停止LaravelS |
| `restart` | 重启LaravelS，支持选项`-d`和`--daemonize` |
| `reload` | 平滑重启所有worker进程，这些worker进程内包含你的业务代码和框架(Laravel/Lumen)代码，不会重启master/manger进程 |
| `publish` | 发布配置文件到你的项目中`config/laravels.php` |

## 部署
> 建议通过[Supervisord](http://supervisord.org/)监管主进程，前提是不能加`-d`选项并且设置`swoole.daemonize`为`false`。

```
[program:laravel-s-test]
command=/user/local/bin/php /opt/www/laravel-s-test/artisan laravels start -i
numprocs=1
autostart=true
autorestart=true
startretries=3
user=www-data
redirect_stderr=true
stdout_logfile=/opt/www/laravel-s-test/storage/logs/supervisord-stdout.log
stopasgroup=true
killasgroup=true
```

## 与Nginx配合使用（推荐）
> [示例](https://github.com/hhxsv5/docker/blob/master/compose/nginx)。

```nginx
gzip on;
gzip_min_length 1024;
gzip_comp_level 2;
gzip_types text/plain text/css text/javascript application/json application/javascript application/x-javascript application/xml application/x-httpd-php image/jpeg image/gif image/png font/ttf font/otf image/svg+xml;
gzip_vary on;
gzip_disable "msie6";
upstream laravels {
    # 通过 IP:Port 连接
    server 127.0.0.1:5200 weight=5 max_fails=3 fail_timeout=30s;
    # 通过 UnixSocket Stream 连接，小诀窍：将socket文件放在/dev/shm目录下，可获得更好的性能
    #server unix:/xxxpath/laravel-s-test/storage/laravels.sock weight=5 max_fails=3 fail_timeout=30s;
    #server 192.168.1.1:5200 weight=3 max_fails=3 fail_timeout=30s;
    #server 192.168.1.2:5200 backup;
    keepalive 16;
}
server {
    listen 80;
    # 别忘了绑Host哟
    server_name laravels.com;
    root /xxxpath/laravel-s-test/public;
    access_log /yyypath/log/nginx/$server_name.access.log  main;
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
        proxy_pass http://laravels;
    }
}
```

## 与Apache配合使用

```apache
LoadModule proxy_module /yyypath/modules/mod_deflate.so
<IfModule deflate_module>
    SetOutputFilter DEFLATE
    DeflateCompressionLevel 2
    AddOutputFilterByType DEFLATE text/html text/plain text/css text/javascript application/json application/javascript application/x-javascript application/xml application/x-httpd-php image/jpeg image/gif image/png font/ttf font/otf image/svg+xml
</IfModule>

<VirtualHost *:80>
    # 别忘了绑Host哟
    ServerName www.laravels.com
    ServerAdmin hhxsv5@sina.com

    DocumentRoot /xxxpath/laravel-s-test/public;
    DirectoryIndex index.html index.htm
    <Directory "/">
        AllowOverride None
        Require all granted
    </Directory>

    LoadModule proxy_module /yyypath/modules/mod_proxy.so
    LoadModule proxy_module /yyypath/modules/mod_proxy_balancer.so
    LoadModule proxy_module /yyypath/modules/mod_lbmethod_byrequests.so.so
    LoadModule proxy_module /yyypath/modules/mod_proxy_http.so.so
    LoadModule proxy_module /yyypath/modules/mod_slotmem_shm.so
    LoadModule proxy_module /yyypath/modules/mod_rewrite.so

    ProxyRequests Off
    ProxyPreserveHost On
    <Proxy balancer://laravels>  
        BalancerMember http://192.168.1.1:8011 loadfactor=7
        #BalancerMember http://192.168.1.2:8011 loadfactor=3
        #BalancerMember http://192.168.1.3:8011 loadfactor=1 status=+H
        ProxySet lbmethod=byrequests
    </Proxy>
    #ProxyPass / balancer://laravels/
    #ProxyPassReverse / balancer://laravels/

    # Apache处理静态资源，LaravelS处理动态资源。
    RewriteEngine On
    RewriteCond %{DOCUMENT_ROOT}%{REQUEST_FILENAME} !-d
    RewriteCond %{DOCUMENT_ROOT}%{REQUEST_FILENAME} !-f
    RewriteRule ^/(.*)$ balancer://laravels/%{REQUEST_URI} [P,L]

    ErrorLog ${APACHE_LOG_DIR}/www.laravels.com.error.log
    CustomLog ${APACHE_LOG_DIR}/www.laravels.com.access.log combined
</VirtualHost>
```
## 启用WebSocket服务器
> WebSocket服务器监听的IP和端口与Http服务器相同。

1.创建WebSocket Handler类，并实现接口`WebSocketHandlerInterface`。start时会自动实例化，不需要手动创建示例。

```php
namespace App\Services;
use Hhxsv5\LaravelS\Swoole\WebSocketHandlerInterface;
/**
 * @see https://wiki.swoole.com/wiki/page/400.html
 */
class WebSocketService implements WebSocketHandlerInterface
{
    // 声明没有参数的构造函数
    public function __construct()
    {
    }
    public function onOpen(\swoole_websocket_server $server, \swoole_http_request $request)
    {
        // 在触发onOpen事件之前Laravel的生命周期已经完结，所以Laravel的Request是可读的，Session是可读写的
        \Log::info('New WebSocket connection', [$request->fd, request()->all(), session()->getId(), session('xxx'), session(['yyy' => time()])]);
        $server->push($request->fd, 'Welcome to LaravelS');
        // throw new \Exception('an exception');// 此时抛出的异常上层会忽略，并记录到Swoole日志，需要开发者try/catch捕获处理
    }
    public function onMessage(\swoole_websocket_server $server, \swoole_websocket_frame $frame)
    {
        \Log::info('Received message', [$frame->fd, $frame->data, $frame->opcode, $frame->finish]);
        $server->push($frame->fd, date('Y-m-d H:i:s'));
        // throw new \Exception('an exception');// 此时抛出的异常上层会忽略，并记录到Swoole日志，需要开发者try/catch捕获处理
    }
    public function onClose(\swoole_websocket_server $server, $fd, $reactorId)
    {
        // throw new \Exception('an exception');// 此时抛出的异常上层会忽略，并记录到Swoole日志，需要开发者try/catch捕获处理
    }
}
```

2.更改配置`config/laravels.php`。

```php
// ...
'websocket'      => [
    'enable'  => true, // 看清楚，这里是true
    'handler' => \App\Services\WebSocketService::class,
],
'swoole'         => [
    //...
    // dispatch_mode只能设置为2、4、5，https://wiki.swoole.com/wiki/page/277.html
    'dispatch_mode' => 2,
    //...
],
// ...
```

3.使用`swoole_table`绑定FD与UserId，可选的，[Swoole Table示例](https://github.com/hhxsv5/laravel-s/blob/master/README-CN.md#%E4%BD%BF%E7%94%A8swoole_table)。也可以用其他全局存储服务，例如Redis/Memcached/MySQL，但需要注意多个`Swoole Server`实例时FD可能冲突。

4.与Nginx配合使用（推荐）
> 参考 [WebSocket代理](http://nginx.org/en/docs/http/websocket.html)

```nginx
map $http_upgrade $connection_upgrade {
    default upgrade;
    ''      close;
}
upstream laravels {
    # 通过 IP:Port 连接
    server 127.0.0.1:5200 weight=5 max_fails=3 fail_timeout=30s;
    # 通过 UnixSocket Stream 连接，小诀窍：将socket文件放在/dev/shm目录下，可获得更好的性能
    #server unix:/xxxpath/laravel-s-test/storage/laravels.sock weight=5 max_fails=3 fail_timeout=30s;
    #server 192.168.1.1:5200 weight=3 max_fails=3 fail_timeout=30s;
    #server 192.168.1.2:5200 backup;
    keepalive 16;
}
server {
    listen 80;
    # 别忘了绑Host哟
    server_name laravels.com;
    root /xxxpath/laravel-s-test/public;
    access_log /yyypath/log/nginx/$server_name.access.log  main;
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
        proxy_pass http://laravels;
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
        proxy_pass http://laravels;
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

## 监听事件

### 系统事件
> 通常，你可以在这些事件中重置或销毁一些全局或静态的变量，也可以修改当前的请求和响应。

- `laravels.received_request` 将`swoole_http_request`转成`Illuminate\Http\Request`后，在Laravel内核处理请求前。

    ```php
    // 修改`app/Providers/EventServiceProvider.php`, 添加下面监听代码到boot方法中
    // 如果变量$events不存在，你也可以通过Facade调用\Event::listen()。
    $events->listen('laravels.received_request', function (\Illuminate\Http\Request $req, $app) {
        $req->query->set('get_key', 'hhxsv5');// 修改querystring
        $req->request->set('post_key', 'hhxsv5'); // 修改post body
    });
    ```

- `laravels.generated_response` 在Laravel内核处理完请求后，将`Illuminate\Http\Response`转成`swoole_http_response`之前(下一步将响应给客户端)。

    ```php
    // 修改`app/Providers/EventServiceProvider.php`, 添加下面监听代码到boot方法中
    // 如果变量$events不存在，你也可以通过Facade调用\Event::listen()。
    $events->listen('laravels.generated_response', function (\Illuminate\Http\Request $req, \Symfony\Component\HttpFoundation\Response $rsp, $app) {
        $rsp->headers->set('header-key', 'hhxsv5');// 修改header
    });
    ```

### 自定义的异步事件
> 此特性依赖`Swoole`的`AsyncTask`，必须先设置`config/laravels.php`的`swoole.task_worker_num`。异步事件的处理能力受Task进程数影响，需合理设置[task_worker_num](https://wiki.swoole.com/wiki/page/276.html)。

1.创建事件类。

```php
use Hhxsv5\LaravelS\Swoole\Task\Event;
class TestEvent extends Event
{
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
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Hhxsv5\LaravelS\Swoole\Task\Event;
use Hhxsv5\LaravelS\Swoole\Task\Listener;
class TestListener1 extends Listener
{
    // 声明没有参数的构造函数
    public function __construct()
    {
    }
    public function handle(Event $event)
    {
        \Log::info(__CLASS__ . ':handle start', [$event->getData()]);
        sleep(2);// 模拟一些慢速的事件处理
        // 监听器中也可以投递Task，但不支持Task的finish()回调。
        // 注意：
        // 1.参数2需传true
        // 2.config/laravels.php中修改配置task_ipc_mode为1或2，参考 https://wiki.swoole.com/wiki/page/296.html
        $ret = Task::deliver(new TestTask('task data'), true);
        var_dump($ret);
        // throw new \Exception('an exception');// handle时抛出的异常上层会忽略，并记录到Swoole日志，需要开发者try/catch捕获处理
    }
}
```

3.绑定事件与监听器。

```php
// 在"config/laravels.php"中绑定事件与监听器，一个事件可以有多个监听器，多个监听器按顺序执行
[
    // ...
    'events' => [
        \App\Tasks\TestEvent::class => [
            \App\Tasks\TestListener1::class,
            //\App\Tasks\TestListener2::class,
        ],
    ],
    // ...
];
```

4.触发事件。

```php
// 实例化TestEvent并通过fire触发，此操作是异步的，触发后立即返回，由Task进程继续处理监听器中的handle逻辑
use Hhxsv5\LaravelS\Swoole\Task\Event;
$success = Event::fire(new TestEvent('event data'));
var_dump($success);//判断是否触发成功
```

## 异步的任务队列
> 此特性依赖`Swoole`的`AsyncTask`，必须先设置`config/laravels.php`的`swoole.task_worker_num`。异步任务的处理能力受Task进程数影响，需合理设置[task_worker_num](https://wiki.swoole.com/wiki/page/276.html)。

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
        // throw new \Exception('an exception');// handle时抛出的异常上层会忽略，并记录到Swoole日志，需要开发者try/catch捕获处理
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
// $task->delay(3);// 延迟3秒投放任务
$ret = Task::deliver($task);
var_dump($ret);//判断是否投递成功
```

## 毫秒级定时任务
> 基于[Swoole的毫秒定时器](https://wiki.swoole.com/wiki/page/244.html)，封装的定时任务，取代`Linux`的`Crontab`。

1.创建定时任务类。
```php
namespace App\Jobs\Timer;
use App\Tasks\TestTask;
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
        $this->i++;
        \Log::info(__METHOD__, ['end', $this->i, microtime(true)]);

        if ($this->i >= 10) { // 运行10次后不再执行
            \Log::info(__METHOD__, ['stop', $this->i, microtime(true)]);
            $this->stop(); // 终止此任务
            // CronJob中也可以投递Task，但不支持Task的finish()回调。
            // 注意：
            // 1.参数2需传true
            // 2.config/laravels.php中修改配置task_ipc_mode为1或2，参考 https://wiki.swoole.com/wiki/page/296.html
            $ret = Task::deliver(new TestTask('task data'), true);
            var_dump($ret);
        }
        // throw new \Exception('an exception');// 此时抛出的异常上层会忽略，并记录到Swoole日志，需要开发者try/catch捕获处理
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
    ],
    // ...
];
```

3.注意在构建服务器集群时，会启动多个`定时器`，要确保只启动一个定期器，避免重复执行定时任务。

## 修改代码后自动Reload

- 基于`inotify`，仅支持Linux。

    1.安装[inotify](http://pecl.php.net/package/inotify)扩展。

    2.开启[配置项](https://github.com/hhxsv5/laravel-s/blob/master/Settings.md)。

    3.注意：`inotify`只有在`Linux`内修改文件才能收到文件变更事件，建议使用最新版Docker，[Vagrant解决方案](https://github.com/mhallin/vagrant-notify-forwarder)。

- 基于`fswatch`，支持OS X、Linux、Windows。

    1.安装[fswatch](https://github.com/emcrisostomo/fswatch)。

    2.在项目根目录下运行命令。

    ```bash
    # 监听当前目录
    ./vendor/bin/fswatch
    # 监听app目录
    ./vendor/bin/fswatch ./app
    ```

## 在你的项目中使用`swoole_server`实例

```php
/**
 * 如果启用WebSocket server，$swoole是`swoole_websocket_server`的实例，否则是是`\swoole_http_server`的实例
 * @var \swoole_http_server|\swoole_websocket_server $swoole
 */
$swoole = app('swoole');
var_dump($swoole->stats());// 单例
```

## 使用`swoole_table`

1.定义`swoole_table`，支持定义多个Table。
> Swoole启动之前会创建定义的所有Table。

```php
// 在"config/laravels.php"配置`swoole_table`
[
    // ...
    'swoole_tables'  => [
        // 场景：WebSocket中UserId与FD绑定
        'ws' => [// Key为Table名称，使用时会自动添加Table后缀，避免重名。这里定义名为wsTable的Table
            'size'   => 102400,//Table的最大行数
            'column' => [// Table的列定义
                ['name' => 'value', 'type' => \swoole_table::TYPE_INT, 'size' => 8],
            ],
        ],
        //...继续定义其他Table
    ],
    // ...
];
```

2.访问`swoole_table`：所有的Table实例均绑定在`swoole_server`上，通过`app('swoole')->xxxTable`访问。

```php
// 场景：WebSocket中UserId与FD绑定
public function onOpen(\swoole_websocket_server $server, \swoole_http_request $request)
{
    // var_dump(app('swoole') === $server);// 同一实例
    $userId = mt_rand(1000, 10000);
    app('swoole')->wsTable->set('uid:' . $userId, ['value' => $request->fd]);// 绑定uid到fd的映射
    app('swoole')->wsTable->set('fd:' . $request->fd, ['value' => $userId]);// 绑定fd到uid的映射
    $server->push($request->fd, 'Welcome to LaravelS');
}
public function onMessage(\swoole_websocket_server $server, \swoole_websocket_frame $frame)
{
    foreach (app('swoole')->wsTable as $key => $row) {
        if (strpos($key, 'uid:') === 0 && $server->exist($row['value'])) {
            $server->push($row['value'], 'Broadcast: ' . date('Y-m-d H:i:s'));// 广播
        }
    }
}
public function onClose(\swoole_websocket_server $server, $fd, $reactorId)
{
    $uid = app('swoole')->wsTable->get('fd:' . $fd);
    if ($uid !== false) {
        app('swoole')->wsTable->del('uid:' . $uid['value']);// 解绑uid映射
    }
    app('swoole')->wsTable->del('fd:' . $fd);// 解绑fd映射
    $server->push($fd, 'Goodbye');
}
```

## 多端口混合协议

> 更多的信息，请参考[Swoole增加监听的端口](https://wiki.swoole.com/wiki/page/16.html)与[多端口混合协议](https://wiki.swoole.com/wiki/page/525.html)

为了使我们的主服务器能支持除`HTTP`和`WebSocket`外的更多协议，我们引入了`Swoole`的`多端口混合协议`特性，在LaravelS中称为`Socket`。现在，可以很方便地在`Laravel`上被构建`TCP/UDP`应用。

1. 创建Socket处理类，继承`Hhxsv5\LaravelS\Swoole\Socket\{TcpSocket|UdpSocket|Http|WebSocket}`

    ```php
    namespace App\Sockets;
    use Hhxsv5\LaravelS\Swoole\Socket\TcpSocket;
    class TestTcpSocket extends TcpSocket
    {
        public function onConnect(\swoole_server $server, $fd, $reactorId)
        {
            \Log::info('New TCP connection', [$fd]);
            $server->send($fd, 'Welcome to LaravelS.');
        }
        public function onReceive(\swoole_server $server, $fd, $reactorId, $data)
        {
            \Log::info('Received data', [$fd, $data]);
            $server->send($fd, 'LaravelS: ' . $data);
            if ($data === "quit\r\n") {
                $server->send($fd, 'LaravelS: bye' . PHP_EOL);
                $server->close($fd);
            }
        }
        public function onClose(\swoole_server $server, $fd, $reactorId)
        {
            \Log::info('Close TCP connection', [$fd]);
            $server->send($fd, 'Goodbye');
        }
    }
    ```

    这些连接和主服务器上的HTTP/WebSocket连接共享Worker进程，因此可以在这些事件操作中使用LaravelS提供的`异步任务投递`、`swoole_table`、Laravel提供的组件如`DB`、`Eloquent`等。同时，如果需要使用该协议端口的`swoole_server_port`对象，只需要像如下代码一样访问`Socket`类的成员`swoolePort`即可。

    ```php
    public function onReceive(\swoole_server $server, $fd, $reactorId, $data)
    {
        $port = $this->swoolePort; //获得`swoole_server_port`对象
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
            'type'     => SWOOLE_SOCK_TCP,// 支持的嵌套字类型：https://wiki.swoole.com/wiki/page/16.html#entry_h2_0
            'settings' => [// Swoole可用的配置项：https://wiki.swoole.com/wiki/page/526.html
                'open_eof_check' => true,
                'package_eof'    => "\r\n",
            ],
            'handler'  => \App\Sockets\TestTcpSocket::class,
        ],
    ],
    ```

    关于心跳配置，只能设置在`主服务器`上，不能配置在`套接字`上，但`套接字`会继承`主服务器`的心跳配置。

    对于TCP协议，`dispatch_mode`选项设为`1/3`时，底层会屏蔽`onConnect`/`onClose`事件，原因是这两种模式下无法保证`onConnect`/`onClose`/`onReceive`的顺序。如果需要用到这两个事件，请将`dispatch_mode`改为`2/4/5`，[参考](https://wiki.swoole.com/wiki/page/277.html)。

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

    - WebSocket
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

> [Swoole原始文档](https://wiki.swoole.com/wiki/page/749.html)

- 警告：Laravel/Lumen中存在大量单例和静态属性，在协程下是`不安全`的，不建议打开协程。

- 启用协程，默认是关闭的。
    
    ```php
    // 修改文件 `config/laravels.php`
    [
        //...
        'swoole' => [
            //...
            'enable_coroutine' => true
         ],
    ]
    ```

- [协程客户端](https://wiki.swoole.com/wiki/page/p-coroutine_mysql.html)：需`Swoole>=2.0`。

- [运行时协程](https://wiki.swoole.com/wiki/page/965.html)：需`Swoole>=4.1.0`，同时启用下面的配置。

    ```php
    // 修改文件 `config/laravels.php`
    [
        //...
        'enable_coroutine_runtime' => true
    ]
    ```

## 自定义进程

> 支持开发者创建一些特殊的工作进程，用于监控、上报或者其他特殊的任务，参考[addProcess](https://wiki.swoole.com/wiki/page/214.html)。

1. 创建Proccess类，实现CustomProcessInterface接口。

    ```php
    namespace App\Processes;
    use App\Tasks\TestTask;
    use Hhxsv5\LaravelS\Swoole\Task\Task;
    use Hhxsv5\LaravelS\Swoole\Process\CustomProcessInterface;
    class TestProcess implements CustomProcessInterface
    {
        public static function getName()
        {
            // 进程名称
            return 'test';
        }
        public static function isRedirectStdinStdout()
        {
            // 是否重定向输入输出
            return false;
        }
        public static function getPipeType()
        {
            // 管道类型：0不创建管道，1创建SOCK_STREAM类型管道，2创建SOCK_DGRAM类型管道
            return 0;
        }
        public static function callback(\swoole_server $swoole)
        {
            // 进程运行的代码，不能退出，一旦退出Manager进程会自动再次创建该进程。
            \Log::info(__METHOD__, [posix_getpid(), $swoole->stats()]);
            while (true) {
                \Log::info('Do something');
                sleep(1);
                // 自定义进程中也可以投递Task，但不支持Task的finish()回调。
                // 注意：
                // 1.参数2需传true
                // 2.config/laravels.php中修改配置task_ipc_mode为1或2，参考 https://wiki.swoole.com/wiki/page/296.html
                $ret = Task::deliver(new TestTask('task data'), true);
                var_dump($ret);
                // 上层会捕获callback中抛出的异常，并记录到Swoole日志，如果异常数达到10次，此进程会退出，Manager进程会重新创建进程，所以建议开发者自行try/catch捕获，避免创建进程过于频繁。
                // throw new \Exception('an exception');
            }
        }
    }
    ```

2. 注册TestProcess。

    ```php
    // 修改文件 config/laravels.php
    // ...
    'processes' => [
        \App\Processes\TestProcess::class,
    ],
    ```

3. 注意：TestProcess::callback()方法不能退出，如果退出次数达到10次，Manager进程将会重新创建进程。

## 注意事项

- `单例问题`
    - 传统FPM下，单例模式的对象的生命周期仅在每次请求中，请求开始=>实例化单例=>请求结束后=>单例对象资源回收。

    - Swoole Server下，所有单例对象会常驻于内存，这个时候单例对象的生命周期与FPM不同，请求开始=>实例化单例=>请求结束=>单例对象依旧保留，需要开发者自己维护单例的状态。

    - 常见的解决方案：

        1. 用一个`中间件`来`重置`单例对象的状态。

        2. 如果是以`ServiceProvider`注册的单例对象，可添加该`ServiceProvider`到`laravels.php`的`register_providers`中，这样每次请求会重新注册该`ServiceProvider`，重新实例化单例对象，[参考](https://github.com/hhxsv5/laravel-s/blob/master/Settings-CN.md)。

- [常见问题](https://github.com/hhxsv5/laravel-s/blob/master/KnownIssues-CN.md)

- 应通过`Illuminate\Http\Request`对象来获取请求信息，$_ENV是可读取的，`不能使用`$_GET、$_POST、$_FILES、$_COOKIE、$_REQUEST、$_SESSION、$GLOBALS、$_SERVER。

    ```php
    public function form(\Illuminate\Http\Request $request)
    {
        $name = $request->input('name');
        $all = $request->all();
        $sessionId = $request->cookie('sessionId');
        $photo = $request->file('photo');
        $rawContent = $request->getContent();
        //...
    }
    ```

- 推荐通过返回`Illuminate\Http\Response`对象来响应请求，兼容echo、vardump()、print_r()，`不能使用`函数像 dd()、exit()、die()、header()、setcookie()、http_response_code()。

    ```php
    public function json()
    {
        return response()->json(['time' => time()])->header('header1', 'value1')->withCookie('c1', 'v1');
    }
    ```

- 各种`单例的连接`将被常驻内存，建议开启`持久连接`。
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
        //...
    ],
    //...
    ```

2. Redis连接，连接断开后`不会立即`自动重连，会抛出一个关于连接断开的异常，下次会自动重连。需确保每次操作Redis前正确的`SELECT DB`。
    ```php
    // config/database.php
    'redis' => [
            'default' => [
                'host'       => env('REDIS_HOST', 'localhost'),
                'password'   => env('REDIS_PASSWORD', null),
                'port'       => env('REDIS_PORT', 6379),
                'database'   => 0,
                'persistent' => true, // 开启持久连接
            ],
        ],
    //...
    ```

- 你声明的全局、静态变量必须手动清理或重置。

- 无限追加元素到静态或全局变量中，将导致内存爆满。

    ```php
    // 某类
    class Test
    {
        public static $array = [];
        public static $string = '';
    }

    // 某控制器
    public function test(Request $req)
    {
        // 内存爆满
        Test::$array[] = $req->input('param1');
        Test::$string .= $req->input('param2');
    }
    ```

- [Linux内核参数调整](https://wiki.swoole.com/wiki/page/p-server/sysctl.html)

- [压力测试](https://wiki.swoole.com/wiki/page/62.html)

## 用户与案例

- [医联用户端Passport](https://www.medlinker.com/)：WEB站、M站、APP、小程序的账户体系服务。
    
    <img src="https://user-images.githubusercontent.com/7278743/46649457-af05e980-cbcb-11e8-94a1-b13d743d33fd.png" height="300px" alt="医联Passport服务">

- [ITOK在线客服平台](http://demo.topitsm.com)：用户IT工单的处理跟踪及在线实时沟通。
    
    <img src="https://user-images.githubusercontent.com/7278743/46649548-10c65380-cbcc-11e8-81e6-f4a8dca2eb2c.png" height="300px" alt="ITOK在线客服平台">

- [盟呱呱](http://mgg.yamecent.com)
    
    <img src="https://user-images.githubusercontent.com/7278743/46648932-b3310780-cbc9-11e8-971e-ca26e3378507.png" height="300px" alt="盟呱呱">

- 微信公众号-广州塔：活动、商城
    
    <img src="https://user-images.githubusercontent.com/7278743/46649832-1a9c8680-cbcd-11e8-902e-978fa644f4d9.png" height="300px" alt="广州塔">

- 企鹅游戏盒子、明星新势力、以及小程序广告服务
    
    <img src="https://user-images.githubusercontent.com/7278743/46649296-2c7d2a00-cbcb-11e8-94d3-bc12fc9566d6.jpg" height="300px" alt="企鹅游戏盒子">

- 小程序-修机匠手机上门维修服务：手机维修服务，提供上门服务，支持在线维修。
    
    <img src="https://user-images.githubusercontent.com/7278743/46941544-eda11580-d09d-11e8-8c3a-16c567655277.png" height="300px" alt="修机匠手机上门维修服务">

- 亿健APP

## 待办事项

1. 针对MySQL/Redis的连接池。

## 其他选择

- [swooletw/laravel-swoole](https://github.com/swooletw/laravel-swoole)

## 打赏
> 您的支持是我们坚持的最大动力。

<img src="https://raw.githubusercontent.com/hhxsv5/laravel-s/master/reward.png" height="300px" alt="打赏">

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

## License

[MIT](https://github.com/hhxsv5/laravel-s/blob/master/LICENSE)
