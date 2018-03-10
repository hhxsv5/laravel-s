# LaravelS - 站在巨人的肩膀上
> 🚀 通过Swoole来加速 Laravel/Lumen，其中的S代表Swoole，速度，高性能。

[![Latest Stable Version](https://poser.pugx.org/hhxsv5/laravel-s/v/stable.svg)](https://packagist.org/packages/hhxsv5/laravel-s)
[![Total Downloads](https://poser.pugx.org/hhxsv5/laravel-s/downloads.svg)](https://packagist.org/packages/hhxsv5/laravel-s)
[![Latest Unstable Version](https://poser.pugx.org/hhxsv5/laravel-s/v/unstable.svg)](https://packagist.org/packages/hhxsv5/laravel-s)
[![License](https://poser.pugx.org/hhxsv5/laravel-s/license.svg)](https://github.com/hhxsv5/laravel-s/blob/master/LICENSE)
[![Build Status](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/badges/build.png?b=master)](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/?branch=master)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)
<!-- [![Code Coverage](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/?branch=master) -->

**[English Documentation](https://github.com/hhxsv5/laravel-s/blob/master/README.md)**  *QQ交流群：698480528*

## 特性

- 高性能的Swoole

- 内置Http/Websocket服务器

- 常驻内存

- 异步的事件监听

- 异步的任务队列

- 平滑Reload

- 代码修改后自动Reload

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

2.添加Service Provider。

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
    
    # Nginx处理静态资源，LaravelS处理动态资源。
    location / {
        try_files $uri @laravels;
    }

    location @laravels {
        proxy_http_version 1.1;
        # proxy_connect_timeout 60s;
        # proxy_send_timeout 60s;
        # proxy_read_timeout 120s;
        proxy_set_header Connection "keep-alive";
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Real-PORT $remote_port;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Host $host;
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
## 启用Websocket服务器
> Websocket服务器监听的IP和端口与Http服务器相同。

1.创建Websocket Handler类，并实现接口`WebsocketHandlerInterface`。
```PHP
namespace App\Services;
use Hhxsv5\LaravelS\Swoole\WebsocketHandlerInterface;
/**
 * @see https://wiki.swoole.com/wiki/page/400.html
 */
class WebsocketService implements WebsocketHandlerInterface
{
    public function onOpen(\swoole_websocket_server $server, \swoole_http_request $request)
    {
        \Log::info('New Websocket connection', [$request->fd]);
        $server->push($request->fd, 'Welcome to LaravelS');
        // throw new \Exception('an exception'); //上层会自动忽略handle时抛出的异常
    }
    public function onMessage(\swoole_websocket_server $server, \swoole_websocket_frame $frame)
    {
        \Log::info('Received message', [$frame->fd, $frame->data, $frame->opcode, $frame->finish]);
        $server->push($frame->fd, date('Y-m-d H:i:s'));
    }
    public function onClose(\swoole_websocket_server $server, $fd, $reactorId)
    {
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
// ...
```

## 监听事件

### 系统事件
> 通常，你可以在这些事件中重置或销毁一些全局或静态的变量，也可以修改当前的请求和响应。

- `laravels.received_request` 将`swoole_http_request`转成`Illuminate\Http\Request`后，在Laravel内核处理请求前。

```PHP
// 修改`app/Providers/EventServiceProvider.php`, 添加下面监听代码到boot方法中
// 如果变量$exents不存在，你也可以调用\Event::listen()。
$events->listen('laravels.received_request', function (\Illuminate\Http\Request $req) {
    $req->query->set('get_key', 'hhxsv5');// 修改querystring
    $req->request->set('post_key', 'hhxsv5'); // 修改post body
});
```

- `laravels.generated_response` 在Laravel内核处理完请求后，将`Illuminate\Http\Response`转成`swoole_http_response`之前(下一步将响应给客户端)。

```PHP
// 修改`app/Providers/EventServiceProvider.php`, 添加下面监听代码到boot方法中
// 如果变量$exents不存在，你也可以调用\Event::listen()。
$events->listen('laravels.generated_response', function (\Illuminate\Http\Request $req, \Symfony\Component\HttpFoundation\Response $rsp) {
    $rsp->headers->set('header-key', 'hhxsv5');// 修改header
});
```

### 自定义的异步事件
> 事件监听的处理能力受Task进程数影响，需合理设置[task_worker_num](https://wiki.swoole.com/wiki/page/276.html)。

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
    public function handle(Event $event)
    {
        \Log::info(__CLASS__ . ':handle start', [$event->getData()]);
        sleep(2);// 模拟一些慢速的事件处理
        // throw new \Exception('an exception'); //上层会自动忽略handle时抛出的异常
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
> 异步任务的处理能力受Task进程数影响，需合理设置[task_worker_num](https://wiki.swoole.com/wiki/page/276.html)。

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
        // throw new \Exception('an exception'); //上层会自动忽略handle时抛出的异常
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

## 在你的项目中使用`swoole_http_server`实例

```PHP
/**
* @var \swoole_http_server
*/
$swoole = app('swoole');// Singleton
var_dump($swoole->stats());
```

## 注意事项

- 推荐通过`Illuminate\Http\Request`对象来获取请求信息，兼容$_SERVER、$_GET、$_POST、$_FILES、$_COOKIE、$_REQUEST，`不能使用`$_SESSION、$_ENV。

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

## [已知的兼容性问题](https://github.com/hhxsv5/laravel-s/blob/master/KnownCompatibleIssues-CN.md)

## 待办事项

1. 针对MySQL/Redis的连接池。

2. 包装MySQL/Redis/Http的协程客户端。

## 打赏
<img src="https://github.com/hhxsv5/laravel-s/blob/master/reward.png" height="200px" alt="打赏">

## License

[MIT](https://github.com/hhxsv5/laravel-s/blob/master/LICENSE)
