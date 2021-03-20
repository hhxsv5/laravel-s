```
 _                               _  _____ 
| |                             | |/ ____|
| |     __ _ _ __ __ ___   _____| | (___  
| |    / _` | '__/ _` \ \ / / _ \ |\___ \ 
| |___| (_| | | | (_| |\ V /  __/ |____) |
|______\__,_|_|  \__,_| \_/ \___|_|_____/ 
                                           
```
> 🚀 LaravelS is `an out-of-the-box adapter` between Swoole and Laravel/Lumen.

*Please `Watch` this repository to get the latest updates.*

[![Latest Stable Version](https://poser.pugx.org/hhxsv5/laravel-s/v/stable.svg)](https://packagist.org/packages/hhxsv5/laravel-s)
[![Latest Unstable Version](https://poser.pugx.org/hhxsv5/laravel-s/v/unstable.svg)](https://packagist.org/packages/hhxsv5/laravel-s)
[![Total Downloads](https://poser.pugx.org/hhxsv5/laravel-s/downloads.svg)](https://packagist.org/packages/hhxsv5/laravel-s)
[![License](https://poser.pugx.org/hhxsv5/laravel-s/license.svg)](https://github.com/hhxsv5/laravel-s/blob/master/LICENSE)
[![Build Status](https://travis-ci.com/hhxsv5/laravel-s.svg?branch=master)](https://travis-ci.com/hhxsv5/laravel-s)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)
<!-- [![Total Lines](https://tokei.rs/b1/github/hhxsv5/laravel-s)](https://github.com/hhxsv5/laravel-s) -->
<!-- [![Code Coverage](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/?branch=master) -->

**[中文文档](https://github.com/hhxsv5/laravel-s/blob/master/README-CN.md)**

Table of Contents
=================

* [Features](#features)
* [Benchmark](#benchmark)
* [Requirements](#requirements)
* [Install](#install)
* [Run](#run)
* [Deploy](#deploy)
* [Cooperate with Nginx (Recommended)](#cooperate-with-nginx-recommended)
* [Cooperate with Apache](#cooperate-with-apache)
* [Enable WebSocket server](#enable-websocket-server)
* [Listen events](#listen-events)
    * [System events](#system-events)
    * [Customized asynchronous events](#customized-asynchronous-events)
* [Asynchronous task queue](#asynchronous-task-queue)
* [Millisecond cron job](#millisecond-cron-job)
* [Automatically reload after modifying code](#automatically-reload-after-modifying-code)
* [Get the instance of SwooleServer in your project](#get-the-instance-of-swooleserver-in-your-project)
* [Use SwooleTable](#use-swooletable)
* [Multi-port mixed protocol](#multi-port-mixed-protocol)
* [Coroutine](#coroutine)
* [Custom process](#custom-process)
* [Common components](#common-components)
    * [Apollo](#apollo)
* [Other features](#other-features)
    * [Configure Swoole events](#configure-swoole-events)
    * [Serverless](#serverless)
* [Important notices](#important-notices)
* [Users and cases](https://github.com/hhxsv5/laravel-s/blob/master/README-CN.md#%E7%94%A8%E6%88%B7%E4%B8%8E%E6%A1%88%E4%BE%8B)
* [Alternatives](#alternatives)
* [Sponsor](#sponsor)
* [License](#license)

## Features

- Built-in Http/[WebSocket](https://github.com/hhxsv5/laravel-s/blob/master/README.md#enable-websocket-server) server

- [Multi-port mixed protocol](https://github.com/hhxsv5/laravel-s/blob/master/README.md#multi-port-mixed-protocol)

- [Custom process](https://github.com/hhxsv5/laravel-s/blob/master/README.md#custom-process)

- Memory resident

- [Asynchronous event listening](https://github.com/hhxsv5/laravel-s/blob/master/README.md#customized-asynchronous-events)

- [Asynchronous task queue](https://github.com/hhxsv5/laravel-s/blob/master/README.md#asynchronous-task-queue)

- [Millisecond cron job](https://github.com/hhxsv5/laravel-s/blob/master/README.md#millisecond-cron-job)

- [Common Components](https://github.com/hhxsv5/laravel-s/blob/master/README.md#common-components)

- Gracefully reload

- [Automatically reload after modifying code](https://github.com/hhxsv5/laravel-s/blob/master/README.md#automatically-reload-after-modifying-code)

- Support Laravel/Lumen both, good compatibility

- Simple & Out of the box

## Benchmark

- [Which is the fastest web framework?](https://github.com/the-benchmarker/web-frameworks)

- [TechEmpower Framework Benchmarks](https://www.techempower.com/benchmarks/)

## Requirements

| Dependency | Requirement |
| -------- | -------- |
| [PHP](https://secure.php.net/manual/en/install.php) | `>= 5.5.9` `Recommend PHP7+` |
| [Swoole](https://www.swoole.co.uk/) | `>= 1.7.19` `No longer support PHP5 since 2.0.12` `Recommend 4.2.3+` |
| [Laravel](https://laravel.com/)/[Lumen](https://lumen.laravel.com/) | `>= 5.1` `Recommend 5.6+` |

## Install

1.Require package via [Composer](https://getcomposer.org/)([packagist](https://packagist.org/packages/hhxsv5/laravel-s)).
```bash
composer require "hhxsv5/laravel-s:~3.7.0" -vvv
# Make sure that your composer.lock file is under the VCS
```

2.Register service provider(pick one of two).

- `Laravel`: in `config/app.php` file, `Laravel 5.5+ supports package discovery automatically, you should skip this step`
    ```php
    'providers' => [
        //...
        Hhxsv5\LaravelS\Illuminate\LaravelSServiceProvider::class,
    ],
    ```

- `Lumen`: in `bootstrap/app.php` file
    ```php
    $app->register(Hhxsv5\LaravelS\Illuminate\LaravelSServiceProvider::class);
    ```

3.Publish configuration and binaries.
> *After upgrading LaravelS, you need to republish; click [here](https://github.com/hhxsv5/laravel-s/releases) to see the change notes of each version.*

```bash
php artisan laravels publish
# Configuration: config/laravels.php
# Binary: bin/laravels bin/fswatch bin/inotify
```

4.Change `config/laravels.php`: listen_ip, listen_port, refer [Settings](https://github.com/hhxsv5/laravel-s/blob/master/Settings.md).

5.Performance tuning

- [Adjust kernel parameters](https://wiki.swoole.com/#/other/sysctl?id=%e5%86%85%e6%a0%b8%e5%8f%82%e6%95%b0%e8%b0%83%e6%95%b4)

- [Number of Workers](https://www.swoole.co.uk/docs/modules/swoole-server/configuration#worker_num): LaravelS uses Swoole's `Synchronous IO` mode, so the larger the `worker_num` setting, the better the concurrency performance, but it will also cause more memory usage and process switching overhead, so incremental pressure testing is needed to get the best` worker_num`.

- [Number of Task Workers](https://www.swoole.co.uk/docs/modules/swoole-server/configuration#task_worker_num)

## Run
> `Please read the notices carefully before running`, [Important notices](https://github.com/hhxsv5/laravel-s#important-notices)(IMPORTANT).

- Commands: `php bin/laravels {start|stop|restart|reload|info|help}`.

| Command | Description |
| --------- | --------- |
| start | Start LaravelS, list the processes by "*ps -ef&#124;grep laravels*" |
| stop | Stop LaravelS, and trigger the method `onStop` of Custom process |
| restart | Restart LaravelS: Stop gracefully before starting; The service is `unavailable` until startup is complete |
| reload | Reload all Task/Worker/Timer processes which contain your business codes, and trigger the method `onReload` of Custom process, CANNOT reload Master/Manger processes. After modifying `config/laravels.php`, you `only` have to call `restart` to restart |
| info | Display component version information |
| help | Display help information |

- Boot options for the commands `start` and `restart`.

| Option | Description |
| --------- | --------- |
| -d&#124;--daemonize | Run as a daemon, this option will override the `swoole.daemonize` setting in `laravels.php` |
| -e&#124;--env | The environment the command should run under, such as `--env=testing` will use the configuration file `.env.testing` firstly, this feature requires Laravel 5.2+ |
| -i&#124;--ignore | Ignore checking PID file of Master process |
| -x&#124;--x-version | The version(branch) of the current project, stored in $_ENV/$_SERVER, access via `$_ENV['X_VERSION']` `$_SERVER['X_VERSION']` `$request->server->get('X_VERSION')` |

- `Runtime` files: `start` will automatically execute `php artisan laravels config` and generate these files, developers generally don't need to pay attention to them, it's recommended to add them to `.gitignore`.

| File | Description |
| --------- | --------- |
| storage/laravels.conf | LaravelS's `runtime` configuration file |
| storage/laravels.pid | PID file of Master process |
| storage/laravels-timer-process.pid | PID file of the Timer process |
| storage/laravels-custom-processes.pid | PID file of all custom processes |

## Deploy
> It is recommended to supervise the main process through [Supervisord](http://supervisord.org/), the premise is without option `-d` and to set `swoole.daemonize` to `false`.

```
[program:laravel-s-test]
directory=/var/wwww/laravel-s-test
command=/usr/local/bin/php bin/laravels start -i
numprocs=1
autostart=true
autorestart=true
startretries=3
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/%(program_name)s.log
```

## Cooperate with Nginx (Recommended)
> [Demo](https://github.com/hhxsv5/docker/blob/master/nginx/conf.d/laravels.conf).

```nginx
gzip on;
gzip_min_length 1024;
gzip_comp_level 2;
gzip_types text/plain text/css text/javascript application/json application/javascript application/x-javascript application/xml application/x-httpd-php image/jpeg image/gif image/png font/ttf font/otf image/svg+xml;
gzip_vary on;
gzip_disable "msie6";
upstream swoole {
    # Connect IP:Port
    server 127.0.0.1:5200 weight=5 max_fails=3 fail_timeout=30s;
    # Connect UnixSocket Stream file, tips: put the socket file in the /dev/shm directory to get better performance
    #server unix:/yourpath/laravel-s-test/storage/laravels.sock weight=5 max_fails=3 fail_timeout=30s;
    #server 192.168.1.1:5200 weight=3 max_fails=3 fail_timeout=30s;
    #server 192.168.1.2:5200 backup;
    keepalive 16;
}
server {
    listen 80;
    # Don't forget to bind the host
    server_name laravels.com;
    root /yourpath/laravel-s-test/public;
    access_log /yourpath/log/nginx/$server_name.access.log  main;
    autoindex off;
    index index.html index.htm;
    # Nginx handles the static resources(recommend enabling gzip), LaravelS handles the dynamic resource.
    location / {
        try_files $uri @laravels;
    }
    # Response 404 directly when request the PHP file, to avoid exposing public/*.php
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
        # "swoole" is the upstream
        proxy_pass http://swoole;
    }
}
```

## Cooperate with Apache

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
    # Don't forget to bind the host
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

    # Apache handles the static resources, LaravelS handles the dynamic resource.
    RewriteEngine On
    RewriteCond %{DOCUMENT_ROOT}%{REQUEST_FILENAME} !-d
    RewriteCond %{DOCUMENT_ROOT}%{REQUEST_FILENAME} !-f
    RewriteRule ^/(.*)$ balancer://laravels/%{REQUEST_URI} [P,L]

    ErrorLog ${APACHE_LOG_DIR}/www.laravels.com.error.log
    CustomLog ${APACHE_LOG_DIR}/www.laravels.com.access.log combined
</VirtualHost>
```

## Enable WebSocket server
> The Listening address of WebSocket Sever is the same as Http Server.

1.Create WebSocket Handler class, and implement interface `WebSocketHandlerInterface`.The instant is automatically instantiated when start, you do not need to manually create it.
```php
namespace App\Services;
use Hhxsv5\LaravelS\Swoole\WebSocketHandlerInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;
/**
 * @see https://www.swoole.co.uk/docs/modules/swoole-websocket-server
 */
class WebSocketService implements WebSocketHandlerInterface
{
    // Declare constructor without parameters
    public function __construct()
    {
    }
    // public function onHandShake(Request $request, Response $response)
    // {
           // Custom handshake: https://www.swoole.co.uk/docs/modules/swoole-websocket-server-on-handshake
           // The onOpen event will be triggered automatically after a successful handshake
    // }
    public function onOpen(Server $server, Request $request)
    {
        // Before the onOpen event is triggered, the HTTP request to establish the WebSocket has passed the Laravel route,
        // so Laravel's Request, Auth information are readable, Session is readable and writable, but only in the onOpen event.
        // \Log::info('New WebSocket connection', [$request->fd, request()->all(), session()->getId(), session('xxx'), session(['yyy' => time()])]);
        // The exceptions thrown here will be caught by the upper layer and recorded in the Swoole log. Developers need to try/catch manually.
        $server->push($request->fd, 'Welcome to LaravelS');
    }
    public function onMessage(Server $server, Frame $frame)
    {
        // \Log::info('Received message', [$frame->fd, $frame->data, $frame->opcode, $frame->finish]);
        // The exceptions thrown here will be caught by the upper layer and recorded in the Swoole log. Developers need to try/catch manually.
        $server->push($frame->fd, date('Y-m-d H:i:s'));
    }
    public function onClose(Server $server, $fd, $reactorId)
    {
        // The exceptions thrown here will be caught by the upper layer and recorded in the Swoole log. Developers need to try/catch manually.
    }
}
```

2.Modify `config/laravels.php`.
```php
// ...
'websocket'      => [
    'enable'  => true, // Note: set enable to true
    'handler' => \App\Services\WebSocketService::class,
],
'swoole'         => [
    //...
    // Must set dispatch_mode in (2, 4, 5), see https://www.swoole.co.uk/docs/modules/swoole-server/configuration
    'dispatch_mode' => 2,
    //...
],
// ...
```
3.Use `SwooleTable` to bind FD & UserId, optional, [Swoole Table Demo](https://github.com/hhxsv5/laravel-s/blob/master/README.md#use-swooletable). Also you can use the other global storage services, like Redis/Memcached/MySQL, but be careful that FD will be possible conflicting between multiple `Swoole Servers`.

4.Cooperate with Nginx (Recommended)
> Refer [WebSocket Proxy](http://nginx.org/en/docs/http/websocket.html)

```nginx
map $http_upgrade $connection_upgrade {
    default upgrade;
    ''      close;
}
upstream swoole {
    # Connect IP:Port
    server 127.0.0.1:5200 weight=5 max_fails=3 fail_timeout=30s;
    # Connect UnixSocket Stream file, tips: put the socket file in the /dev/shm directory to get better performance
    #server unix:/yourpath/laravel-s-test/storage/laravels.sock weight=5 max_fails=3 fail_timeout=30s;
    #server 192.168.1.1:5200 weight=3 max_fails=3 fail_timeout=30s;
    #server 192.168.1.2:5200 backup;
    keepalive 16;
}
server {
    listen 80;
    # Don't forget to bind the host
    server_name laravels.com;
    root /yourpath/laravel-s-test/public;
    access_log /yourpath/log/nginx/$server_name.access.log  main;
    autoindex off;
    index index.html index.htm;
    # Nginx handles the static resources(recommend enabling gzip), LaravelS handles the dynamic resource.
    location / {
        try_files $uri @laravels;
    }
    # Response 404 directly when request the PHP file, to avoid exposing public/*.php
    #location ~* \.php$ {
    #    return 404;
    #}
    # Http and WebSocket are concomitant, Nginx identifies them by "location"
    # !!! The location of WebSocket is "/ws"
    # Javascript: var ws = new WebSocket("ws://laravels.com/ws");
    location =/ws {
        # proxy_connect_timeout 60s;
        # proxy_send_timeout 60s;
        # proxy_read_timeout: Nginx will close the connection if the proxied server does not send data to Nginx in 60 seconds; At the same time, this close behavior is also affected by heartbeat setting of Swoole.
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

5.Heartbeat setting

- Heartbeat setting of Swoole

    ```php
    // config/laravels.php
    'swoole' => [
        //...
        // All connections are traversed every 60 seconds. If a connection does not send any data to the server within 600 seconds, the connection will be forced to close.
        'heartbeat_idle_time'      => 600,
        'heartbeat_check_interval' => 60,
        //...
    ],
    ```

- Proxy read timeout of Nginx

    ```nginx
    # Nginx will close the connection if the proxied server does not send data to Nginx in 60 seconds
    proxy_read_timeout 60s;
    ```

6.Push data in controller

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

## Listen events

### System events
> Usually, you can reset/destroy some `global/static` variables, or change the current `Request/Response` object.

- `laravels.received_request` After LaravelS parsed `Swoole\Http\Request` to `Illuminate\Http\Request`, before Laravel's Kernel handles this request.

    ```php
    // Edit file `app/Providers/EventServiceProvider.php`, add the following code into method `boot`
    // If no variable $events, you can also call Facade \Event::listen(). 
    $events->listen('laravels.received_request', function (\Illuminate\Http\Request $req, $app) {
        $req->query->set('get_key', 'hhxsv5');// Change query of request
        $req->request->set('post_key', 'hhxsv5'); // Change post of request
    });
    ```

- `laravels.generated_response` After Laravel's Kernel handled the request, before LaravelS parses `Illuminate\Http\Response` to `Swoole\Http\Response`.

    ```php
    // Edit file `app/Providers/EventServiceProvider.php`, add the following code into method `boot`
    // If no variable $events, you can also call Facade \Event::listen(). 
    $events->listen('laravels.generated_response', function (\Illuminate\Http\Request $req, \Symfony\Component\HttpFoundation\Response $rsp, $app) {
        $rsp->headers->set('header-key', 'hhxsv5');// Change header of response
    });
    ```

### Customized asynchronous events
> This feature depends on `AsyncTask` of `Swoole`, your need to set `swoole.task_worker_num` in `config/laravels.php` firstly. The performance of asynchronous event processing is influenced by number of Swoole task process, you need to set [task_worker_num](https://www.swoole.co.uk/docs/modules/swoole-server/configuration) appropriately.

1.Create event class.
```php
use Hhxsv5\LaravelS\Swoole\Task\Event;
class TestEvent extends Event
{
    protected $listeners = [
        // Listener list
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

2.Create listener class.
```php
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Hhxsv5\LaravelS\Swoole\Task\Listener;
class TestListener1 extends Listener
{
    /**
     * @var TestEvent
     */
    protected $event;
    
    public function handle()
    {
        \Log::info(__CLASS__ . ':handle start', [$this->event->getData()]);
        sleep(2);// Simulate the slow codes
        // Deliver task in CronJob, but NOT support callback finish() of task.
        // Note: Modify task_ipc_mode to 1 or 2 in config/laravels.php, see https://www.swoole.co.uk/docs/modules/swoole-server/configuration
        $ret = Task::deliver(new TestTask('task data'));
        var_dump($ret);
        // The exceptions thrown here will be caught by the upper layer and recorded in the Swoole log. Developers need to try/catch manually.
    }
}
```

3.Fire event.
```php
// Create instance of event and fire it, "fire" is asynchronous.
use Hhxsv5\LaravelS\Swoole\Task\Event;
$event = new TestEvent('event data');
// $event->delay(10); // Delay 10 seconds to fire event
// $event->setTries(3); // When an error occurs, try 3 times in total
$success = Event::fire($event);
var_dump($success);// Return true if sucess, otherwise false
```

## Asynchronous task queue
> This feature depends on `AsyncTask` of `Swoole`, your need to set `swoole.task_worker_num` in `config/laravels.php` firstly. The performance of task processing is influenced by number of Swoole task process, you need to set [task_worker_num](https://www.swoole.co.uk/docs/modules/swoole-server/configuration) appropriately.

1.Create task class.
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
    // The logic of task handling, run in task process, CAN NOT deliver task
    public function handle()
    {
        \Log::info(__CLASS__ . ':handle start', [$this->data]);
        sleep(2);// Simulate the slow codes
        // The exceptions thrown here will be caught by the upper layer and recorded in the Swoole log. Developers need to try/catch manually.
        $this->result = 'the result of ' . $this->data;
    }
    // Optional, finish event, the logic of after task handling, run in worker process, CAN deliver task 
    public function finish()
    {
        \Log::info(__CLASS__ . ':finish start', [$this->result]);
        Task::deliver(new TestTask2('task2 data')); // Deliver the other task
    }
}
```

2.Deliver task.
```php
// Create instance of TestTask and deliver it, "deliver" is asynchronous.
use Hhxsv5\LaravelS\Swoole\Task\Task;
$task = new TestTask('task data');
// $task->delay(3);// delay 3 seconds to deliver task
// $task->setTries(3); // When an error occurs, try 3 times in total
$ret = Task::deliver($task);
var_dump($ret);// Return true if sucess, otherwise false
```

## Millisecond cron job
> Wrapper cron job base on [Swoole's Millisecond Timer](https://www.swoole.co.uk/docs/modules/swoole-async-io), replace `Linux` `Crontab`.

1.Create cron job class.
```php
namespace App\Jobs\Timer;
use App\Tasks\TestTask;
use Swoole\Coroutine;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Hhxsv5\LaravelS\Swoole\Timer\CronJob;
class TestCronJob extends CronJob
{
    protected $i = 0;
    // !!! The `interval` and `isImmediate` of cron job can be configured in two ways(pick one of two): one is to overload the corresponding method, and the other is to pass parameters when registering cron job.
    // --- Override the corresponding method to return the configuration: begin
    public function interval()
    {
        return 1000;// Run every 1000ms
    }
    public function isImmediate()
    {
        return false;// Whether to trigger `run` immediately after setting up
    }
    // --- Override the corresponding method to return the configuration: end
    public function run()
    {
        \Log::info(__METHOD__, ['start', $this->i, microtime(true)]);
        // do something
        // sleep(1); // Swoole < 2.1
        Coroutine::sleep(1); // Swoole>=2.1 Coroutine will be automatically created for run().
        $this->i++;
        \Log::info(__METHOD__, ['end', $this->i, microtime(true)]);

        if ($this->i >= 10) { // Run 10 times only
            \Log::info(__METHOD__, ['stop', $this->i, microtime(true)]);
            $this->stop(); // Stop this cron job, but it will run again after restart/reload.
            // Deliver task in CronJob, but NOT support callback finish() of task.
            // Note: Modify task_ipc_mode to 1 or 2 in config/laravels.php, see https://www.swoole.co.uk/docs/modules/swoole-server/configuration
            $ret = Task::deliver(new TestTask('task data'));
            var_dump($ret);
        }
        // The exceptions thrown here will be caught by the upper layer and recorded in the Swoole log. Developers need to try/catch manually.
    }
}
```

2.Register cron job.
```php
// Register cron jobs in file "config/laravels.php"
[
    // ...
    'timer'          => [
        'enable' => true, // Enable Timer
        'jobs'   => [ // The list of cron job
            // Enable LaravelScheduleJob to run `php artisan schedule:run` every 1 minute, replace Linux Crontab
            // \Hhxsv5\LaravelS\Illuminate\LaravelScheduleJob::class,
            // Two ways to configure parameters:
            // [\App\Jobs\Timer\TestCronJob::class, [1000, true]], // Pass in parameters when registering
            \App\Jobs\Timer\TestCronJob::class, // Override the corresponding method to return the configuration
        ],
        'max_wait_time' => 5, // Max waiting time of reloading
        // Enable the global lock to ensure that only one instance starts the timer when deploying multiple instances. This feature depends on Redis, please see https://laravel.com/docs/7.x/redis
        'global_lock'     => false,
        'global_lock_key' => config('app.name', 'Laravel'),
    ],
    // ...
];
```

3.Note: it will launch multiple timers when build the server cluster, so you need to make sure that launch one timer only to avoid running repetitive task.

4.LaravelS `v3.4.0` starts to support the hot restart [Reload] `Timer` process. After LaravelS receives the `SIGUSR1` signal, it waits for `max_wait_time`(default 5) seconds to end the process, then the `Manager` process will pull up the `Timer` process again.

5.If you only need to use `minute-level` scheduled tasks, it is recommended to enable `Hhxsv5\LaravelS\Illuminate\LaravelScheduleJob` instead of Linux Crontab, so that you can follow the coding habits of [Laravel task scheduling](https://laravel.com/docs/7.x/scheduling) and configure `Kernel`.

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // runInBackground() will start a new child process to execute the task. This is asynchronous and will not affect the execution timing of other tasks.
    $schedule->command(TestCommand::class)->runInBackground()->everyMinute();
}
```

## Automatically reload after modifying code

- Via `inotify`, support Linux only.

    1.Install [inotify](http://pecl.php.net/package/inotify) extension.

    2.Turn on the switch in [Settings](https://github.com/hhxsv5/laravel-s/blob/master/Settings.md#inotify_reloadenable).

    3.Notice: Modify the file only in `Linux` to receive the file change events. It's recommended to use the latest Docker. [Vagrant Solution](https://github.com/mhallin/vagrant-notify-forwarder).

- Via `fswatch`, support OS X/Linux/Windows.

    1.Install [fswatch](https://github.com/emcrisostomo/fswatch).

    2.Run command in your project root directory.

    ```bash
    # Watch current directory
    ./bin/fswatch
    # Watch app directory
    ./bin/fswatch ./app
    ```

- Via `inotifywait`, support Linux.

    1.Install [inotify-tools](https://github.com/rvoicilas/inotify-tools).

    2.Run command in your project root directory.

    ```bash
    # Watch current directory
    ./bin/inotify
    # Watch app directory
    ./bin/inotify ./app
    ```

- When the above methods does not work, the ultimate solution: set `max_request=1,worker_num=1`, so that `Worker` process will restart after processing a request. The performance of this method is very poor, `so only development environment use`.

## Get the instance of `SwooleServer` in your project

```php
/**
 * $swoole is the instance of `Swoole\WebSocket\Server` if enable WebSocket server, otherwise `Swoole\Http\Server`
 * @var \Swoole\WebSocket\Server|\Swoole\Http\Server $swoole
 */
$swoole = app('swoole');
var_dump($swoole->stats());
$swoole->push($fd, 'Push WebSocket message');
```

## Use `SwooleTable`

1.Define Table, support multiple.
> All defined tables will be created before Swoole starting.

```php
// in file "config/laravels.php"
[
    // ...
    'swoole_tables'  => [
        // Scene：bind UserId & FD in WebSocket
        'ws' => [// The Key is table name, will add suffix "Table" to avoid naming conflicts. Here defined a table named "wsTable"
            'size'   => 102400,// The max size
            'column' => [// Define the columns
                ['name' => 'value', 'type' => \Swoole\Table::TYPE_INT, 'size' => 8],
            ],
        ],
        //...Define the other tables
    ],
    // ...
];
```

2.Access `Table`: all table instances will be bound on `SwooleServer`, access by `app('swoole')->xxxTable`.

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
    // Scene：bind UserId & FD in WebSocket
    public function onOpen(Server $server, Request $request)
    {
        // var_dump(app('swoole') === $server);// The same instance
        /**
         * Get the currently logged in user
         * This feature requires that the path to establish a WebSocket connection go through middleware such as Authenticate.
         * E.g:
         * Browser side: var ws = new WebSocket("ws://127.0.0.1:5200/ws");
         * Then the /ws route in Laravel needs to add the middleware like Authenticate.
         * Route::get('/ws', function () {
         *     // Respond any content with status code 200
         *     return 'websocket';
         * })->middleware(['auth']);
         */
        // $user = Auth::user();
        // $userId = $user ? $user->id : 0; // 0 means a guest user who is not logged in
        $userId = mt_rand(1000, 10000);
        // if (!$userId) {
        //     // Disconnect the connections of unlogged users
        //     $server->disconnect($request->fd);
        //     return;
        // }
        $this->wsTable->set('uid:' . $userId, ['value' => $request->fd]);// Bind map uid to fd
        $this->wsTable->set('fd:' . $request->fd, ['value' => $userId]);// Bind map fd to uid
        $server->push($request->fd, "Welcome to LaravelS #{$request->fd}");
    }
    public function onMessage(Server $server, Frame $frame)
    {
        // Broadcast
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
            $this->wsTable->del('uid:' . $uid['value']); // Unbind uid map
        }
        $this->wsTable->del('fd:' . $fd);// Unbind fd map
        $server->push($fd, "Goodbye #{$fd}");
    }
}
```

## Multi-port mixed protocol

> For more information, please refer to [Swoole Server AddListener](https://www.swoole.co.uk/docs/modules/swoole-server-methods#swoole_server-addlistener)

To make our main server support more protocols not just Http and WebSocket, we bring the feature `multi-port mixed protocol` of Swoole in LaravelS and name it `Socket`. Now, you can build `TCP/UDP` applications easily on top of Laravel.

1. Create `Socket` handler class, and extend `Hhxsv5\LaravelS\Swoole\Socket\{TcpSocket|UdpSocket|Http|WebSocket}`.

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

    These `Socket` connections share the same worker processes with your `HTTP`/`WebSocket` connections. So it won't be a problem at all if you want to deliver tasks, use `SwooleTable`, even Laravel components such as DB, Eloquent and so on.
    At the same time, you can access `Swoole\Server\Port` object directly by member property `swoolePort`.

    ```php
    public function onReceive(Server $server, $fd, $reactorId, $data)
    {
        $port = $this->swoolePort; // Get the `Swoole\Server\Port` object
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
            // $swoole->ports: Traverse all Port objects, https://www.swoole.co.uk/docs/modules/swoole-server/multiple-ports
            $port = $swoole->ports[0]; // Get the `Swoole\Server\Port` object, $port[0] is the port of the main server
            foreach ($port->connections as $fd) { // Traverse all connections
                // $swoole->send($fd, 'Send tcp message');
                // if($swoole->isEstablished($fd)) {
                //     $swoole->push($fd, 'Send websocket message');
                // }
            }
        }
    }
    ```

2. Register Sockets.

    ```php
    // Edit `config/laravels.php`
    //...
    'sockets' => [
        [
            'host'     => '127.0.0.1',
            'port'     => 5291,
            'type'     => SWOOLE_SOCK_TCP,// Socket type: SWOOLE_SOCK_TCP/SWOOLE_SOCK_TCP6/SWOOLE_SOCK_UDP/SWOOLE_SOCK_UDP6/SWOOLE_UNIX_DGRAM/SWOOLE_UNIX_STREAM
            'settings' => [// Swoole settings：https://www.swoole.co.uk/docs/modules/swoole-server-methods#swoole_server-addlistener
                'open_eof_check' => true,
                'package_eof'    => "\r\n",
            ],
            'handler'  => \App\Sockets\TestTcpSocket::class,
            'enable'   => true, // whether to enable, default true
        ],
    ],
    ```

    About the heartbeat configuration, it can only be set on the `main server` and cannot be configured on `Socket`, but the `Socket` inherits the heartbeat configuration of the `main server`.

    For TCP socket, `onConnect` and `onClose` events will be blocked when `dispatch_mode` of Swoole is `1/3`, so if you want to unblock these two events please set `dispatch_mode` to `2/4/5`.

    ```php
    'swoole' => [
        //...
        'dispatch_mode' => 2,
        //...
    ];
    ```

3. Test.

- TCP: `telnet 127.0.0.1 5291`

- UDP: [Linux] `echo "Hello LaravelS" > /dev/udp/127.0.0.1/5292`

4. Register example of other protocols.

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

    - WebSocket: The main server must `turn on WebSocket`, that is, set `websocket.enable` to `true`.
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

## Coroutine

> [Swoole Coroutine](https://www.swoole.co.uk/coroutine)

- Warning: The order of code execution in the coroutine is out of order. The data of the request level should be isolated by the coroutine ID. However, there are many singleton and static attributes in Laravel/Lumen, the data between different requests will affect each other, it's `Unsafe`. For example, the database connection is a singleton, the same database connection shares the same PDO resource. This is fine in the synchronous blocking mode, but it does not work in the asynchronous coroutine mode. Each query needs to create different connections and maintain IO state of different connections, which requires a connection pool.

- `DO NOT` enable the coroutine, only the custom process can use the coroutine.

## Custom process

> Support developers to create special work processes for monitoring, reporting, or other special tasks. Refer [addProcess](https://www.swoole.co.uk/docs/modules/swoole-server-methods#swoole_server-addprocess).

1. Create Proccess class, implements CustomProcessInterface.

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
         * @var bool Quit tag for Reload updates
         */
        private static $quit = false;

        public static function callback(Server $swoole, Process $process)
        {
            // The callback method cannot exit. Once exited, Manager process will automatically create the process 
            while (!self::$quit) {
                \Log::info('Test process: running');
                // sleep(1); // Swoole < 2.1
                Coroutine::sleep(1); // Swoole>=2.1: Coroutine & Runtime will be automatically enabled for callback().
                 // Deliver task in custom process, but NOT support callback finish() of task.
                // Note: Modify task_ipc_mode to 1 or 2 in config/laravels.php, see https://www.swoole.co.uk/docs/modules/swoole-server/configuration
                $ret = Task::deliver(new TestTask('task data'));
                var_dump($ret);
                // The upper layer will catch the exception thrown in the callback and record it in the Swoole log, and then this process will exit. The Manager process will re-create the process after 3 seconds, so developers need to try/catch to catch the exception by themselves to avoid frequent process creation.
                // throw new \Exception('an exception');
            }
        }
        // Requirements: LaravelS >= v3.4.0 & callback() must be async non-blocking program.
        public static function onReload(Server $swoole, Process $process)
        {
            // Stop the process...
            // Then end process
            \Log::info('Test process: reloading');
            self::$quit = true;
            // $process->exit(0); // Force exit process
        }
        // Requirements: LaravelS >= v3.7.4 & callback() must be async non-blocking program.
        public static function onStop(Server $swoole, Process $process)
        {
            // Stop the process...
            // Then end process
            \Log::info('Test process: stopping');
            self::$quit = true;
            // $process->exit(0); // Force exit process
        }
    }
    ```

2. Register TestProcess.

    ```php
    // Edit `config/laravels.php`
    // ...
    'processes' => [
        'test' => [ // Key name is process name
            'class'    => \App\Processes\TestProcess::class,
            'redirect' => false, // Whether redirect stdin/stdout, true or false
            'pipe'     => 0,     // The type of pipeline, 0: no pipeline 1: SOCK_STREAM 2: SOCK_DGRAM
            'enable'   => true,  // Whether to enable, default true
            //'queue'    => [ // Enable message queue as inter-process communication, configure empty array means use default parameters
            //    'msg_key'  => 0,    // The key of the message queue. Default: ftok(__FILE__, 1).
            //    'mode'     => 2,    // Communication mode, default is 2, which means contention mode
            //    'capacity' => 8192, // The length of a single message, is limited by the operating system kernel parameters. The default is 8192, and the maximum is 65536
            //],
            //'restart_interval' => 5, // After the process exits abnormally, how many seconds to wait before restarting the process, default 5 seconds
        ],
    ],
    ```

3. Note: The callback() cannot quit. If quit, the Manager process will re-create the process.

4. Example: Write data to a custom process.

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
        /**@var \Swoole\Process $process */
        $process = app('swoole')->customProcesses['test'];
        $process->write('TestController: write data' . time());
        var_dump($process->read());
    }
    ```

## Common components

### Apollo
> `LaravelS` will pull the `Apollo` configuration and write it to the `.env` file when starting. At the same time, `LaravelS` will start the custom process `apollo` to monitor the configuration and automatically `reload` when the configuration changes.

1. Enable Apollo: add `--enable-apollo` and Apollo parameters to the startup parameters.
    
    ```bash
    php bin/laravels start --enable-apollo --apollo-server=http://127.0.0.1:8080 --apollo-app-id=LARAVEL-S-TEST
    ```

2. Support hot updates(optional).

    ```php
    // Edit `config/laravels.php`
    'processes' => Hhxsv5\LaravelS\Components\Apollo\Process::getDefinition(),
    ```

    ```php
    // When there are other custom process configurations
    'processes' => [
        'test' => [
            'class'    => \App\Processes\TestProcess::class,
            'redirect' => false,
            'pipe'     => 1,
        ],
        // ...
    ] + Hhxsv5\LaravelS\Components\Apollo\Process::getDefinition(),
    ```

3. List of available parameters.

| Parameter | Description | Default | Demo |
| -------- | -------- | -------- | -------- |
| apollo-server | Apollo server URL | - | --apollo-server=http://127.0.0.1:8080 |
| apollo-app-id | Apollo APP ID | - | --apollo-app-id=LARAVEL-S-TEST |
| apollo-namespaces | The namespace to which the APP belongs, support specify the multiple | application | --apollo-namespaces=application --apollo-namespaces=env |
| apollo-cluster | The cluster to which the APP belongs | default | --apollo-cluster=default |
| apollo-client-ip | IP of current instance, can also be used for grayscale publishing | Local intranet IP | --apollo-client-ip=10.2.1.83 |
| apollo-pull-timeout | Timeout time(seconds) when pulling configuration | 5 | --apollo-pull-timeout=5 |
| apollo-backup-old-env | Whether to backup the old configuration file when updating the configuration file `.env` | false | --apollo-backup-old-env |

## Other features

### Configure Swoole events

Supported events:

| Event | Interface | When happened |
| -------- | -------- | -------- |
| ServerStart | Hhxsv5\LaravelS\Swoole\Events\ServerStartInterface | Occurs when the Master process is starting, `this event should not handle complex business logic, and can only do some simple work of initialization`. |
| ServerStop | Hhxsv5\LaravelS\Swoole\Events\ServerStopInterface | Occurs when the server exits normally, `CANNOT use async or coroutine related APIs in this event`. |
| WorkerStart | Hhxsv5\LaravelS\Swoole\Events\WorkerStartInterface | Occurs after the Worker/Task process is started, and the Laravel initialization has been completed. |
| WorkerStop | Hhxsv5\LaravelS\Swoole\Events\WorkerStopInterface | Occurs after the Worker/Task process exits normally |
| WorkerError | Hhxsv5\LaravelS\Swoole\Events\WorkerErrorInterface | Occurs when an exception or fatal error occurs in the Worker/Task process |

1.Create an event class to implement the corresponding interface.
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
        // Initialize a global counter (available across processes)
        $server->atomicCount = new Atomic(2233);

        // Invoked in controller: app('swoole')->atomicCount->get();
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
        // Initialize a database connection pool
        // DatabaseConnectionPool::init();
    }
}
```
2.Configuration.
```php
// Edit `config/laravels.php`
'event_handlers' => [
    'ServerStart' => [\App\Events\ServerStartEvent::class], // Trigger events in array order
    'WorkerStart' => [\App\Events\WorkerStartEvent::class],
],
```

### Serverless

#### Alibaba Cloud Function Compute
> [Function Compute](https://www.alibabacloud.com/help/product/50980.htm).

1.Modify `bootstrap/app.php` and set the storage directory. Because the project directory is read-only, the `/tmp` directory can only be read and written.

```php
$app->useStoragePath(env('APP_STORAGE_PATH', '/tmp/storage'));
```

2.Create a shell script `laravels_bootstrap` and grant `executable permission`.

```bash
#!/usr/bin/env bash
set +e

# Create storage-related directories
mkdir -p /tmp/storage/app/public
mkdir -p /tmp/storage/framework/cache
mkdir -p /tmp/storage/framework/sessions
mkdir -p /tmp/storage/framework/testing
mkdir -p /tmp/storage/framework/views
mkdir -p /tmp/storage/logs

# Set the environment variable APP_STORAGE_PATH, please make sure it's the same as APP_STORAGE_PATH in .env
export APP_STORAGE_PATH=/tmp/storage

# Start LaravelS
php bin/laravels start
```

3.Configure `template.xml`.

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

## Important notices

### Singleton Issue

- Under FPM mode, singleton instances will be instantiated and recycled in every request, request start=>instantiate instance=>request end=>recycled instance.

- Under Swoole Server, All singleton instances will be held in memory, different lifetime from FPM, request start=>instantiate instance=>request end=>do not recycle singleton instance. So need developer to maintain status of singleton instances in every request.

- Common solutions:

    1. Write a `XxxCleaner` class to clean up the singleton object state. This class implements the interface `Hhxsv5\LaravelS\Illuminate\Cleaners\CleanerInterface` and then registers it in `cleaners` of `laravels.php`.

    2. `Reset` status of singleton instances by `Middleware`.

    1. Re-register `ServiceProvider`, add `XxxServiceProvider` into `register_providers` of file `laravels.php`. So that reinitialize singleton instances in every request [Refer](https://github.com/hhxsv5/laravel-s/blob/master/Settings.md#register_providers).

### Cleaners
> [Configuration cleaners](https://github.com/hhxsv5/laravel-s/blob/master/Settings.md#cleaners).

### Known issues
> [Known issues](https://github.com/hhxsv5/laravel-s/blob/master/KnownIssues.md): a package of known issues and solutions.

### Debugging method

- Logging; if you want to output to the console, you can use `stderr`, Log::channel('stderr')->debug('debug message').

- [Laravel Dump Server](https://github.com/beyondcode/laravel-dump-server)(Laravel 5.7 has been integrated by default).

### Read request
Read request by `Illuminate\Http\Request` Object, $_ENV is readable, $_SERVER is partially readable, `CANNOT USE` $_GET/$_POST/$_FILES/$_COOKIE/$_REQUEST/$_SESSION/$GLOBALS.

```php
public function form(\Illuminate\Http\Request $request)
{
    $name = $request->input('name');
    $all = $request->all();
    $sessionId = $request->cookie('sessionId');
    $photo = $request->file('photo');
    // Call getContent() to get the raw POST body, instead of file_get_contents('php://input')
    $rawContent = $request->getContent();
    //...
}
```

### Output response
Respond by `Illuminate\Http\Response` Object, compatible with echo/vardump()/print_r()，`CANNOT USE` functions dd()/exit()/die()/header()/setcookie()/http_response_code().

```php
public function json()
{
    return response()->json(['time' => time()])->header('header1', 'value1')->withCookie('c1', 'v1');
}
```

### Persistent connection
`Singleton connection` will be resident in memory, it is recommended to turn on `persistent connection` for better performance.
1. Database connection, it `will` reconnect automatically `immediately` after disconnect.

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
            // Enable persistent connection
            \PDO::ATTR_PERSISTENT => true,
        ],
    ],
],
```

2. Redis connection, it `won't` reconnect automatically `immediately` after disconnect, and will throw an exception about lost connection, reconnect next time. You need to make sure that `SELECT DB` correctly before operating Redis every time.

```php
// config/database.php
'redis' => [
    'client' => env('REDIS_CLIENT', 'phpredis'), // It is recommended to use phpredis for better performance.
    'default' => [
        'host'       => env('REDIS_HOST', 'localhost'),
        'password'   => env('REDIS_PASSWORD', null),
        'port'       => env('REDIS_PORT', 6379),
        'database'   => 0,
        'persistent' => true, // Enable persistent connection
    ],
],
```

### About memory leaks

- Avoid using global variables. If necessary, please clean or reset them manually.

- Infinitely appending element into `static`/`global` variable will lead to OOM(Out of Memory).

    ```php
    class Test
    {
        public static $array = [];
        public static $string = '';
    }

    // Controller
    public function test(Request $req)
    {
        // Out of Memory
        Test::$array[] = $req->input('param1');
        Test::$string .= $req->input('param2');
    }
    ```

- Memory leak detection method

    1. Modify `config/laravels.php`: `worker_num=1, max_request=1000000`, remember to change it back after test;

    2. Add routing `/debug-memory-leak` without `route middleware` to observe the memory changes of the `Worker` process;

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

    3. Start `LaravelS` and request `/debug-memory-leak` until `diff_mem` is less than or equal to zero; if `diff_mem` is always greater than zero, it means that there may be a memory leak in `Global Middleware` or `Laravel Framework`;
    
    4. After completing `Step 3`, `alternately` request the business routes and `/debug-memory-leak` (It is recommended to use `ab`/`wrk` to make a large number of requests for business routes), the initial increase in memory is normal. After a large number of requests for the business routes, if `diff_mem` is always greater than zero and `curr_mem` continues to increase, there is a high probability of memory leak; If `curr_mem` always changes within a certain range and does not continue to increase, there is a low probability of memory leak.

    5. If you still can't solve it, [max_request](https://www.swoole.co.uk/docs/modules/swoole-server/configuration#max_request) is the last guarantee.

### Linux kernel parameter adjustment
> [Linux kernel parameter adjustment](https://wiki.swoole.com/#/other/sysctl?id=%e5%86%85%e6%a0%b8%e5%8f%82%e6%95%b0%e8%b0%83%e6%95%b4)

### Pressure test
> [Pressure test](https://wiki.swoole.com/wiki/page/62.html)

## Alternatives

- [swooletw/laravel-swoole](https://github.com/swooletw/laravel-swoole)

## Sponsor

- [PayPal](https://www.paypal.me/hhxsv5)

- [BTC](https://www.blockchain.com/btc/address/367HnAzVTAEk8offesDhcsCQswnugsE54u)

- [Gitee](https://gitee.com/hhxsv5/laravel-s?donate=true)

## License

[MIT](https://github.com/hhxsv5/laravel-s/blob/master/LICENSE)
