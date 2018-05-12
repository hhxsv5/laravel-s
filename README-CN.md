```
 _                               _  _____ 
| |                             | |/ ____|
| |     __ _ _ __ __ ___   _____| | (___  
| |    / _` | '__/ _` \ \ / / _ \ |\___ \ 
| |___| (_| | | | (_| |\ V /  __/ |____) |
|______\__,_|_|  \__,_| \_/ \___|_|_____/ 
                                           
```
> 🚀 基于`Swoole`加速Laravel/Lumen，`Swoole`带你飞。

[![Latest Stable Version](https://poser.pugx.org/hhxsv5/laravel-s/v/stable.svg)](https://packagist.org/packages/hhxsv5/laravel-s)
[![Latest Unstable Version](https://poser.pugx.org/hhxsv5/laravel-s/v/unstable.svg)](https://packagist.org/packages/hhxsv5/laravel-s)
[![Total Downloads](https://poser.pugx.org/hhxsv5/laravel-s/downloads.svg)](https://packagist.org/packages/hhxsv5/laravel-s)
[![License](https://poser.pugx.org/hhxsv5/laravel-s/license.svg)](https://github.com/hhxsv5/laravel-s/blob/master/LICENSE)
[![Build Status](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/badges/build.png?b=master)](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/?branch=master)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)
<!-- [![Code Coverage](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/?branch=master) -->

**[English Documentation](https://github.com/hhxsv5/laravel-s/blob/master/README.md)**  *QQ交流群：698480528*

## 特性

- 内置Http/[WebSocket](https://github.com/hhxsv5/laravel-s/blob/master/README-CN.md#%E5%90%AF%E7%94%A8websocket%E6%9C%8D%E5%8A%A1%E5%99%A8)服务器

- [TCP/UDP服务器](https://github.com/hhxsv5/laravel-s/blob/master/README-CN.md#%E5%BC%80%E5%90%AFtcpudp%E6%9C%8D%E5%8A%A1%E5%99%A8)

- 常驻内存

- [异步的事件监听](https://github.com/hhxsv5/laravel-s/blob/master/README-CN.md#%E8%87%AA%E5%AE%9A%E4%B9%89%E7%9A%84%E5%BC%82%E6%AD%A5%E4%BA%8B%E4%BB%B6)

- [异步的任务队列](https://github.com/hhxsv5/laravel-s/blob/master/README-CN.md#%E5%BC%82%E6%AD%A5%E7%9A%84%E4%BB%BB%E5%8A%A1%E9%98%9F%E5%88%97)

- [毫秒级定时任务](https://github.com/hhxsv5/laravel-s/blob/master/README-CN.md#%E6%AF%AB%E7%A7%92%E7%BA%A7%E5%AE%9A%E6%97%B6%E4%BB%BB%E5%8A%A1)

- 平滑Reload

- 修改代码后自动Reload

- 同时支持Laravel与Lumen，兼容主流版本

- 简单，开箱即用

## 要求

| 依赖 | 说明 |
| -------- | -------- |
| [PHP](https://secure.php.net/manual/zh/install.php) | `>= 5.5.9` |
| [Swoole](https://www.swoole.com/) | `>= 1.7.19` `推荐最新的稳定版` `从2.0.12开始不再支持PHP5` |
| [Laravel](https://laravel.com/)/[Lumen](https://lumen.laravel.com/) | `>= 5.1` |
| Gzip[可选的] | [zlib](https://zlib.net/)，用于压缩HTTP响应，检查本机`libz`是否可用 *ldconfig -p&#124;grep libz* |
| Inotify[可选的] | [inotify](http://pecl.php.net/package/inotify)，用于修改代码后自动Reload Worker进程，检查本机`inotify`是否可用 *php --ri inotify* |

## 安装

1.通过[Composer](https://getcomposer.org/)安装([packagist](https://packagist.org/packages/hhxsv5/laravel-s))。

```Bash
# 在你的Laravel/Lumen项目的根目录下执行
composer require "hhxsv5/laravel-s:~1.0" -vvv
# 确保你的composer.lock文件是在版本控制中
```

2.注册Service Provider。

- `Laravel`: 修改文件`config/app.php`
```PHP
'providers' => [
    //...
    Hhxsv5\LaravelS\Illuminate\LaravelSServiceProvider::class,
],
```

- `Lumen`: 修改文件`bootstrap/app.php`
```PHP
$app->register(Hhxsv5\LaravelS\Illuminate\LaravelSServiceProvider::class);
```

3.发布配置文件。
> *每次升级LaravelS后，建议重新发布一次配置文件*
```Bash
php artisan laravels publish
```

`使用Lumen时的特别说明`: 你不需要手动加载配置`laravels.php`，LaravelS底层已自动加载。
```PHP
// 不必手动加载，但加载了也不会有问题
$app->configure('laravels');
```

4.修改配置`config/laravels.php`：监听的IP、端口等，请参考[配置项](https://github.com/hhxsv5/laravel-s/blob/master/Settings-CN.md)。

## 运行
> `php artisan laravels {start|stop|restart|reload|publish}`

| 命令 | 说明 |
| --------- | --------- |
| `start` | 启动LaravelS，展示已启动的进程列表 *ps -ef&#124;grep laravels* |
| `stop` | 停止LaravelS |
| `restart` | 重启LaravelS |
| `reload` | 平滑重启所有worker进程，这些worker进程内包含你的业务代码和框架(Laravel/Lumen)代码，不会重启master/manger进程 |
| `publish` | 发布配置文件到你的项目中`config/laravels.php` |

## 与Nginx配合使用（推荐）

```Nginx
gzip on;
gzip_min_length 1024;
gzip_comp_level 2;
gzip_types text/plain text/css text/javascript application/json application/javascript application/x-javascript application/xml application/x-httpd-php image/jpeg image/gif image/png font/ttf font/otf image/svg+xml;
gzip_vary on;
gzip_disable "msie6";
upstream laravels {
    server 192.168.0.1:5200 weight=5 max_fails=3 fail_timeout=30s;
    #server 192.168.0.2:5200 weight=3 max_fails=3 fail_timeout=30s;
    #server 192.168.0.3:5200 backup;
}
server {
    listen 80;
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
        proxy_http_version 1.1;
        # proxy_connect_timeout 60s;
        # proxy_send_timeout 60s;
        # proxy_read_timeout 120s;
        proxy_set_header Connection "keep-alive";
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

```Apache
LoadModule proxy_module /yyypath/modules/mod_deflate.so
<IfModule deflate_module>
    SetOutputFilter DEFLATE
    DeflateCompressionLevel 2
    AddOutputFilterByType DEFLATE text/html text/plain text/css text/javascript application/json application/javascript application/x-javascript application/xml application/x-httpd-php image/jpeg image/gif image/png font/ttf font/otf image/svg+xml
</IfModule>

<VirtualHost *:80>
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

1.创建WebSocket Handler类，并实现接口`WebsocketHandlerInterface`。
```PHP
namespace App\Services;
use Hhxsv5\LaravelS\Swoole\WebsocketHandlerInterface;
/**
 * @see https://wiki.swoole.com/wiki/page/400.html
 */
class WebsocketService implements WebsocketHandlerInterface
{
    // 声明没有参数的构造函数
    public function __construct()
    {
    }
    public function onOpen(\swoole_websocket_server $server, \swoole_http_request $request)
    {
        \Log::info('New Websocket connection', [$request->fd]);
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
```PHP
// ...
'websocket'      => [
    'enable'  => true,
    'handler' => \App\Services\WebsocketService::class,
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

```Nginx
map $http_upgrade $connection_upgrade {
    default upgrade;
    ''      close;
}
upstream laravels {
    server 192.168.0.1:5200 weight=5 max_fails=3 fail_timeout=30s;
    #server 192.168.0.2:5200 weight=3 max_fails=3 fail_timeout=30s;
    #server 192.168.0.3:5200 backup;
}
server {
    listen 80;
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
    # Http和Websocket共存，Nginx通过location区分
    # Javascript: var ws = new WebSocket("ws://laravels.com/ws");
    location =/ws {
        proxy_http_version 1.1;
        # proxy_connect_timeout 60s;
        # proxy_send_timeout 60s;
        # proxy_read_timeout：如果60秒内客户端没有发数据到服务端，那么Nginx会关闭连接；同时，Swoole的心跳设置也会影响连接的关闭
        # proxy_read_timeout 60s;
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
        proxy_http_version 1.1;
        # proxy_connect_timeout 60s;
        # proxy_send_timeout 60s;
        # proxy_read_timeout 60s;
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

## 监听事件

### 系统事件
> 通常，你可以在这些事件中重置或销毁一些全局或静态的变量，也可以修改当前的请求和响应。

- `laravels.received_request` 将`swoole_http_request`转成`Illuminate\Http\Request`后，在Laravel内核处理请求前。

```PHP
// 修改`app/Providers/EventServiceProvider.php`, 添加下面监听代码到boot方法中
// 如果变量$events不存在，你也可以通过Facade调用\Event::listen()。
$events->listen('laravels.received_request', function (\Illuminate\Http\Request $req, $app) {
    $req->query->set('get_key', 'hhxsv5');// 修改querystring
    $req->request->set('post_key', 'hhxsv5'); // 修改post body
});
```

- `laravels.generated_response` 在Laravel内核处理完请求后，将`Illuminate\Http\Response`转成`swoole_http_response`之前(下一步将响应给客户端)。

```PHP
// 修改`app/Providers/EventServiceProvider.php`, 添加下面监听代码到boot方法中
// 如果变量$events不存在，你也可以通过Facade调用\Event::listen()。
$events->listen('laravels.generated_response', function (\Illuminate\Http\Request $req, \Symfony\Component\HttpFoundation\Response $rsp, $app) {
    $rsp->headers->set('header-key', 'hhxsv5');// 修改header
});
```

### 自定义的异步事件
> 此特性依赖`Swoole`的`AsyncTask`，必须先设置`config/laravels.php`的`swoole.task_worker_num`。异步事件的处理能力受Task进程数影响，需合理设置[task_worker_num](https://wiki.swoole.com/wiki/page/276.html)。

1.创建事件类。
```PHP
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
```PHP
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
        // throw new \Exception('an exception');// handle时抛出的异常上层会忽略，并记录到Swoole日志，需要开发者try/catch捕获处理
    }
}
```

3.绑定事件与监听器。
```PHP
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
```PHP
// 实例化TestEvent并通过fire触发，此操作是异步的，触发后立即返回，由Task进程继续处理监听器中的handle逻辑
use Hhxsv5\LaravelS\Swoole\Task\Event;
$success = Event::fire(new TestEvent('event data'));
var_dump($success);//判断是否触发成功
```

## 异步的任务队列
> 此特性依赖`Swoole`的`AsyncTask`，必须先设置`config/laravels.php`的`swoole.task_worker_num`。异步任务的处理能力受Task进程数影响，需合理设置[task_worker_num](https://wiki.swoole.com/wiki/page/276.html)。

1.创建任务类。
```PHP
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
```PHP
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
```PHP
namespace App\Jobs\Timer;
use Hhxsv5\LaravelS\Swoole\Timer\CronJob;
class TestCronJob extends CronJob
{
    protected $i = 0;
    // 声明没有参数的构造函数
    public function __construct()
    {
    }
    public function interval()
    {
        return 1000;// 每1秒运行一次
    }
    public function run()
    {
        \Log::info(__METHOD__, ['start', $this->i, microtime(true)]);
        // do something
        $this->i++;
        \Log::info(__METHOD__, ['end', $this->i, microtime(true)]);

        if ($this->i >= 10) { // 运行10次后不再执行
            \Log::info(__METHOD__, ['stop', $this->i, microtime(true)]);
            $this->stop(); // 终止此任务
        }
        // throw new \Exception('an exception');// 此时抛出的异常上层会忽略，并记录到Swoole日志，需要开发者try/catch捕获处理
    }
}
```

2.绑定定时任务类。
```PHP
// 在"config/laravels.php"绑定定时任务类
[
    // ...
    'timer'          => [
        'enable' => true, //启用Timer
        'jobs'   => [ //绑定的定时任务类列表
            // 启用LaravelScheduleJob来执行`php artisan schedule:run`，每分钟一次，替代Linux Crontab
            //\Hhxsv5\LaravelS\Illuminate\LaravelScheduleJob::class,
            \App\Jobs\Timer\TestCronJob::class,
        ],
    ],
    // ...
];
```

3.注意在构建服务器集群时，会启动多个`定时器`，要确保只启动一个定期器，避免重复执行定时任务。

## 在你的项目中使用`swoole_server`实例

```PHP
/**
 * 如果启用websocket server，$swoole是`swoole_websocket_server`的实例，否则是是`\swoole_http_server`的实例
 * @var \swoole_http_server|\swoole_websocket_server $swoole
 */
$swoole = app('swoole');
var_dump($swoole->stats());// 单例
```

## 使用`swoole_table`

1.定义`swoole_table`，支持定义多个Table。
> Swoole启动之前会创建定义的所有Table。

```PHP
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
```PHP
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
        if (strpos($key, 'uid:') === 0) {
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

## 开启TCP/UDP服务器

> 更多的信息，请参考 [Swoole Server 增加监听的端口](https://wiki.swoole.com/wiki/page/16.html)与[监听多协议端口](https://wiki.swoole.com/wiki/page/525.html#entry_h2_3)

为了使我们的主服务器能支持除`HTTP`和`Websocket`外的更多协议，我们引入了`Swoole`的`多端口混合协议`特性，在LaravelS中称为`Socket`。现在，可以很方便地在`Laravel`上被构建`TCP/UDP`应用。

1. 创建Socket处理类，继承`Hhxsv5\LaravelS\Swoole\Socket\{Tcp|Udp}Socket`。

```PHP
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
        \Log::info('New TCP connection', [$fd]);
        $server->send($fd, 'Goodbye');
    }
}
```

这些连接和主服务器上的HTTP/Websocket连接共享Worker进程，因此可以在这些事件操作中使用LaravelS提供的`异步任务投递`、`swoole_table`、Laravel提供的组件如`DB`、`Eloquent`等。同时，如果需要使用该协议端口的`swoole_server_port`对象，只需要像如下代码一样访问`Socket`类的成员`swoolePort`即可。

```PHP
public function onReceive(\swoole_server $server, $fd, $reactorId, $data)
{
    $port = $this->swoolePort; //获得`swoole_server_port`对象
}
```

2. 注册套接字。

```PHP
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

对于TCP协议，`dispatch_mode`选项设为`1/3`时，底层会屏蔽`onConnect`/`onClose`事件，原因是这两种模式下无法保证`onConnect`/`onClose`/`onReceive`的顺序。如果需要用到这两个事件，请将`dispatch_mode`改为`2/4/5`，[参考](https://wiki.swoole.com/wiki/page/277.html)。

```PHP
'swoole' => [
    //...
    'dispatch_mode' => 2,
    //...
];
```

3. 测试。

- TCP：`telnet 127.0.0.1 5291`

- UDP：`echo "Hello LaravelS" > /dev/udp/127.0.0.1/5291`

## 注意事项

- [已知的兼容性问题](https://github.com/hhxsv5/laravel-s/blob/master/KnownCompatibleIssues-CN.md)

- 推荐通过`Illuminate\Http\Request`对象来获取请求信息，兼容$_SERVER、$_ENV、$_GET、$_POST、$_FILES、$_COOKIE、$_REQUEST，`不能使用`$_SESSION。

```PHP
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

- 推荐通过返回`Illuminate\Http\Response`对象来响应请求，兼容echo、vardump()、print_r()，`不能使用`函数像exit()、die()、header()、setcookie()、http_response_code()。

```PHP
public function json()
{
    return response()->json(['time' => time()])->header('header1', 'value1')->withCookie('c1', 'v1');
}
```

- 各种`单例的连接`将被常驻内存，建议开启`持久连接`。
1. 数据库连接，连接断开后会自动重连
```PHP
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
```PHP
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

```PHP
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

## 待办事项

1. 针对MySQL/Redis的连接池。

2. 包装MySQL/Redis/Http的协程客户端。

## 其他选择

- [swooletw/laravel-swoole](https://github.com/swooletw/laravel-swoole)

## 打赏
> 您的支持是我们坚持的最大动力。

<img src="https://github.com/hhxsv5/laravel-s/blob/master/reward.png" height="300px" alt="打赏">

### 感谢

| 支持者 | 金额 |
| --- | --- |
| `*思勇 efa***@gmail.com` | 18.88元 |
| `魂之挽歌` | 100元 |
| `小南瓜` | 10.01元 |
| `*丁智` | 16.66元 |

## License

[MIT](https://github.com/hhxsv5/laravel-s/blob/master/LICENSE)
