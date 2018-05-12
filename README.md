```
 _                               _  _____ 
| |                             | |/ ____|
| |     __ _ _ __ __ ___   _____| | (___  
| |    / _` | '__/ _` \ \ / / _ \ |\___ \ 
| |___| (_| | | | (_| |\ V /  __/ |____) |
|______\__,_|_|  \__,_| \_/ \___|_|_____/ 
                                           
```
> 🚀 Speed up Laravel/Lumen by `Swoole`, let's fly.

[![Latest Stable Version](https://poser.pugx.org/hhxsv5/laravel-s/v/stable.svg)](https://packagist.org/packages/hhxsv5/laravel-s)
[![Latest Unstable Version](https://poser.pugx.org/hhxsv5/laravel-s/v/unstable.svg)](https://packagist.org/packages/hhxsv5/laravel-s)
[![Total Downloads](https://poser.pugx.org/hhxsv5/laravel-s/downloads.svg)](https://packagist.org/packages/hhxsv5/laravel-s)
[![License](https://poser.pugx.org/hhxsv5/laravel-s/license.svg)](https://github.com/hhxsv5/laravel-s/blob/master/LICENSE)
[![Build Status](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/badges/build.png?b=master)](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/?branch=master)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)
<!-- [![Code Coverage](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/?branch=master) -->

**[中文文档](https://github.com/hhxsv5/laravel-s/blob/master/README-CN.md)**

## Features

- Built-in Http/[WebSocket](https://github.com/hhxsv5/laravel-s/blob/master/README.md#enable-websocket-server) server

- [TCP/UDP Server](https://github.com/hhxsv5/laravel-s/blob/master/README.md#enable-tcpudp-server)

- Memory resident

- [Asynchronous event listening](https://github.com/hhxsv5/laravel-s/blob/master/README.md#customized-asynchronous-events)

- [Asynchronous task queue](https://github.com/hhxsv5/laravel-s/blob/master/README.md#asynchronous-task-queue)

- [Millisecond cron job](https://github.com/hhxsv5/laravel-s/blob/master/README.md#millisecond-cron-job)


- Gracefully reload

- Automatically reload when code is modified

- Support Laravel/Lumen, good compatibility

- Simple & Out of the box

## Requirements

| Dependency | Requirement |
| -------- | -------- |
| [PHP](https://secure.php.net/manual/en/install.php) | `>= 5.5.9` |
| [Swoole](https://www.swoole.co.uk/) | `>= 1.7.19` `The Newer The Better` `No longer support PHP5 since 2.0.12` |
| [Laravel](https://laravel.com/)/[Lumen](https://lumen.laravel.com/) | `>= 5.1` |
| Gzip[optional] | [zlib](https://zlib.net/), be used to compress the HTTP response, check by *ldconfig -p&#124;grep libz* |
| Inotify[optional] | [inotify](http://pecl.php.net/package/inotify), be used to reload all worker processes when your code is modified, check by *php --ri inotify* |

## Install

1.Require package via [Composer](https://getcomposer.org/)([packagist](https://packagist.org/packages/hhxsv5/laravel-s)).

```Bash
# Run in the root path of your Laravel/Lumen project.
composer require "hhxsv5/laravel-s:~1.0" -vvv
# Make sure that your composer.lock file is under the VCS
```

2.Register service provider.

- `Laravel`: in `config/app.php` file
```PHP
'providers' => [
    //...
    Hhxsv5\LaravelS\Illuminate\LaravelSServiceProvider::class,
],
```

- `Lumen`: in `bootstrap/app.php` file
```PHP
$app->register(Hhxsv5\LaravelS\Illuminate\LaravelSServiceProvider::class);
```

3.Publish configuration.
> *Suggest that do publish after upgrade LaravelS every time*
```Bash
php artisan laravels publish
```

`Special for Lumen`: you `DO NOT` need to load this configuration manually in `bootstrap/app.php` file, LaravelS will load it automatically.
```PHP
// Unnecessary to call configure()
$app->configure('laravels');
```

4.Change `config/laravels.php`: listen_ip, listen_port, refer [Settings](https://github.com/hhxsv5/laravel-s/blob/master/Settings.md).

## Run demo
> `php artisan laravels {start|stop|restart|reload|publish}`

| Command | Description |
| --------- | --------- |
| `start` | Start LaravelS, list the processes by *ps -ef&#124;grep laravels* |
| `stop` | Stop LaravelS |
| `restart` | Restart LaravelS |
| `reload` | Reload all worker processes(Contain your business & Laravel/Lumen codes), exclude master/manger process |
| `publish` | Publish configuration file `laravels.php` into folder `config` |

## Cooperate with Nginx (Recommended)

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
    # Nginx handles the static resources(recommend enabling gzip), LaravelS handles the dynamic resource.
    location / {
        try_files $uri @laravels;
    }
    # Response 404 directly when request the PHP file, to avoid exposing public/*.php
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

## Cooperate with Apache

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

1.Create WebSocket Handler class, and implement interface `WebsocketHandlerInterface`.
```PHP
namespace App\Services;
use Hhxsv5\LaravelS\Swoole\WebsocketHandlerInterface;
/**
 * @see https://www.swoole.co.uk/docs/modules/swoole-websocket-server
 */
class WebsocketService implements WebsocketHandlerInterface
{
    // Declare constructor without parameters
    public function __construct()
    {
    }
    public function onOpen(\swoole_websocket_server $server, \swoole_http_request $request)
    {
        \Log::info('New Websocket connection', [$request->fd]);
        $server->push($request->fd, 'Welcome to LaravelS');
        // throw new \Exception('an exception');// all exceptions will be ignored, then record them into Swoole log, you need to try/catch them
    }
    public function onMessage(\swoole_websocket_server $server, \swoole_websocket_frame $frame)
    {
        \Log::info('Received message', [$frame->fd, $frame->data, $frame->opcode, $frame->finish]);
        $server->push($frame->fd, date('Y-m-d H:i:s'));
        // throw new \Exception('an exception');// all exceptions will be ignored, then record them into Swoole log, you need to try/catch them
    }
    public function onClose(\swoole_websocket_server $server, $fd, $reactorId)
    {
        // throw new \Exception('an exception');// all exceptions will be ignored, then record them into Swoole log, you need to try/catch them
    }
}
```

2.Modify `config/laravels.php`.
```PHP
// ...
'websocket'      => [
    'enable'  => true,
    'handler' => \App\Services\WebsocketService::class,
],
'swoole'         => [
    //...
    // Must set dispatch_mode in (2, 4, 5), see https://www.swoole.co.uk/docs/modules/swoole-server/configuration
    'dispatch_mode' => 2,
    //...
],
// ...
```
3.Use `swoole_table` to bind FD & UserId, optional, [Swoole Table Demo](https://github.com/hhxsv5/laravel-s/blob/master/README.md#use-swoole_table). Also you can use the other global storage services, like Redis/Memcached/MySQL, but be careful that FD will be possible conflicting between multiple `Swoole Servers`.

4.Cooperate with Nginx (Recommended)
> Refer [WebSocket Proxy](http://nginx.org/en/docs/http/websocket.html)

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
    # Nginx handles the static resources(recommend enabling gzip), LaravelS handles the dynamic resource.
    location / {
        try_files $uri @laravels;
    }
    # Response 404 directly when request the PHP file, to avoid exposing public/*.php
    #location ~* \.php$ {
    #    return 404;
    #}
    # Http and Websocket are concomitant, Nginx identifies them by "location"
    # Javascript: var ws = new WebSocket("ws://laravels.com/ws");
    location =/ws {
        proxy_http_version 1.1;
        # proxy_connect_timeout 60s;
        # proxy_send_timeout 60s;
        # proxy_read_timeout: Nginx will close the connection if client does not send data to server in 60 seconds; At the same time, this close behavior is also affected by heartbeat setting of Swoole
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


## Listen events

### System events
> Usually, you can reset/destroy some `global/static` variables, or change the current `Request/Response` object.

- `laravels.received_request` After LaravelS parsed `swoole_http_request` to `Illuminate\Http\Request`, before Laravel's Kernel handles this request.

```PHP
// Edit file `app/Providers/EventServiceProvider.php`, add the following code into method `boot`
// If no variable $events, you can also call Facade \Event::listen(). 
$events->listen('laravels.received_request', function (\Illuminate\Http\Request $req, $app) {
    $req->query->set('get_key', 'hhxsv5');// Change query of request
    $req->request->set('post_key', 'hhxsv5'); // Change post of request
});
```

- `laravels.generated_response` After Laravel's Kernel handled the request, before LaravelS parses `Illuminate\Http\Response` to `swoole_http_response`.

```PHP
// Edit file `app/Providers/EventServiceProvider.php`, add the following code into method `boot`
// If no variable $events, you can also call Facade \Event::listen(). 
$events->listen('laravels.generated_response', function (\Illuminate\Http\Request $req, \Symfony\Component\HttpFoundation\Response $rsp, $app) {
    $rsp->headers->set('header-key', 'hhxsv5');// Change header of response
});
```

### Customized asynchronous events
> This feature depends on `AsyncTask` of `Swoole`, your need to set `swoole.task_worker_num` in `config/laravels.php` firstly. The performance of asynchronous event processing is influenced by number of Swoole task process, you need to set [task_worker_num](https://www.swoole.co.uk/docs/modules/swoole-server/configuration) appropriately.

1.Create event class.
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

2.Create listener class.
```PHP
use Hhxsv5\LaravelS\Swoole\Task\Event;
use Hhxsv5\LaravelS\Swoole\Task\Listener;
class TestListener1 extends Listener
{
    // Declare constructor without parameters
    public function __construct()
    {
    }
    public function handle(Event $event)
    {
        \Log::info(__CLASS__ . ':handle start', [$event->getData()]);
        sleep(2);// Simulate the slow codes
        // throw new \Exception('an exception');// all exceptions will be ignored, then record them into Swoole log, you need to try/catch them
    }
}
```

3.Bind event & listeners.
```PHP
// Bind event & listeners in file "config/laravels.php", one event => many listeners
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

4.Fire event.
```PHP
// Create instance of event and fire it, "fire" is asynchronous.
use Hhxsv5\LaravelS\Swoole\Task\Event;
$success = Event::fire(new TestEvent('event data'));
var_dump($success);// Return true if sucess, otherwise false
```

## Asynchronous task queue
> This feature depends on `AsyncTask` of `Swoole`, your need to set `swoole.task_worker_num` in `config/laravels.php` firstly. The performance of task processing is influenced by number of Swoole task process, you need to set [task_worker_num](https://www.swoole.co.uk/docs/modules/swoole-server/configuration) appropriately.

1.Create task class.
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
    // The logic of task handling, run in task process, CAN NOT deliver task
    public function handle()
    {
        \Log::info(__CLASS__ . ':handle start', [$this->data]);
        sleep(2);// Simulate the slow codes
        // throw new \Exception('an exception');// all exceptions will be ignored, then record them into Swoole log, you need to try/catch them
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
```PHP
// Create instance of TestTask and deliver it, "deliver" is asynchronous.
use Hhxsv5\LaravelS\Swoole\Task\Task;
$task = new TestTask('task data');
// $task->delay(3);// delay 3 seconds to deliver task
$ret = Task::deliver($task);
var_dump($ret);// Return true if sucess, otherwise false
```

## Millisecond cron job
> Wrapper cron job base on [Swoole's Millisecond Timer](https://www.swoole.co.uk/docs/modules/swoole-async-io), replace `Linux` `Crontab`.

1.Create cron job class.
```PHP
namespace App\Jobs\Timer;
use Hhxsv5\LaravelS\Swoole\Timer\CronJob;
class TestCronJob extends CronJob
{
    protected $i = 0;
    // Declare constructor without parameters
    public function __construct()
    {
    }
    public function interval()
    {
        return 1000;// Run every 1000ms
    }
    public function run()
    {
        \Log::info(__METHOD__, ['start', $this->i, microtime(true)]);
        // do something
        $this->i++;
        \Log::info(__METHOD__, ['end', $this->i, microtime(true)]);

        if ($this->i >= 10) { // run 10 times only
            \Log::info(__METHOD__, ['stop', $this->i, microtime(true)]);
            $this->stop(); // stop this cron job
        }
        // throw new \Exception('an exception');// all exceptions will be ignored, then record them into Swoole log, you need to try/catch them
    }
}
```

2.Bind cron job.
```PHP
// Bind cron jobs in file "config/laravels.php"
[
    // ...
    'timer'          => [
        'enable' => true, // Enable Timer
        'jobs'   => [ // the list of cron job
            // Enable LaravelScheduleJob to run `php artisan schedule:run` every 1 minute, replace Linux Crontab
            //\Hhxsv5\LaravelS\Illuminate\LaravelScheduleJob::class,
            \App\Jobs\Timer\TestCronJob::class,
        ],
    ],
    // ...
];
```

3.Note: it will launch multiple timers when build the server cluster, so you need to make sure that launch one timer only to avoid running repetitive task.

## Get the instance of `swoole_server` in your project

```PHP
/**
 * $swoole is the instance of `swoole_websocket_server` if enable websocket server, otherwise `\swoole_http_server`
 * @var \swoole_http_server|\swoole_websocket_server $swoole
 */
$swoole = app('swoole');
var_dump($swoole->stats());// Singleton
```

## Use `swoole_table`

1.Define `swoole_table`, support multiple.
> All defined tables will be created before Swoole starting.

```PHP
// in file "config/laravels.php"
[
    // ...
    'swoole_tables'  => [
        // Scene：bind UserId & FD in WebSocket
        'ws' => [// The Key is table name, will add suffix "Table" to avoid naming conficts. Here defined a table named "wsTable"
            'size'   => 102400,// The max size
            'column' => [// Define the columns
                ['name' => 'value', 'type' => \swoole_table::TYPE_INT, 'size' => 8],
            ],
        ],
        //...Define the other tables
    ],
    // ...
];
```

2.Access `swoole_table`: all table instances will be bound on `swoole_server`, access by `app('swoole')->xxxTable`.
```PHP
// Scene：bind UserId & FD in WebSocket
public function onOpen(\swoole_websocket_server $server, \swoole_http_request $request)
{
    // var_dump(app('swoole') === $server);// The same instance
    $userId = mt_rand(1000, 10000);
    app('swoole')->wsTable->set('uid:' . $userId, ['value' => $request->fd]);// Bind map uid to fd
    app('swoole')->wsTable->set('fd:' . $request->fd, ['value' => $userId]);// Bind map fd to uid
    $server->push($request->fd, 'Welcome to LaravelS');
}
public function onMessage(\swoole_websocket_server $server, \swoole_websocket_frame $frame)
{
    foreach (app('swoole')->wsTable as $key => $row) {
        if (strpos($key, 'uid:') === 0) {
            $server->push($row['value'], 'Broadcast: ' . date('Y-m-d H:i:s'));// Broadcast
        }
    }
}
public function onClose(\swoole_websocket_server $server, $fd, $reactorId)
{
    $uid = app('swoole')->wsTable->get('fd:' . $fd);
    if ($uid !== false) {
        app('swoole')->wsTable->del('uid:' . $uid['value']); // Ubind uid map
    }
    app('swoole')->wsTable->del('fd:' . $fd);// Unbind fd map
    $server->push($fd, 'Goodbye');
}
```

## Enable TCP/UDP Server

> For more information, please refer to [Swoole Server AddListener](https://www.swoole.co.uk/docs/modules/swoole-server-methods#swoole_server-addlistener)

To make our main server support more protocols not just Http and Websocket, we bring the feature `multi-port mixed protocol` of Swoole in LaravelS and name it `Socket`. Now, you can build TCP/UDP applications easily on top of Laravel.

1. Create socket handler class, and extend `Hhxsv5\LaravelS\Swoole\Socket\{Tcp|Udp}Socket`.

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

These `Socket` connections share the same worker processes with your `HTTP`/`Websocket` connections. So it won't be a problem at all if you want to deliver tasks, use `swoole_table`, even Laravel components such as DB, Eloquent and so on.
At the same time, you can access `swoole_server_port` object directly by member property `swoolePort`.

```PHP
public function onReceive(\swoole_server $server, $fd, $reactorId, $data)
{
    $port = $this->swoolePort; //There you go
}
```

2. Register Sockets.

```PHP
// Edit `config/laravels.php`
//...
'sockets' => [
    [
        'host'     => '127.0.0.1',
        'port'     => 5291,
        'type'     => SWOOLE_SOCK_TCP,// Socket type: SWOOLE_SOCK_TCP/SWOOLE_SOCK_UDP
        'settings' => [// Swoole settings：https://www.swoole.co.uk/docs/modules/swoole-server-methods#swoole_server-addlistener
            'open_eof_check' => true,
            'package_eof'    => "\r\n",
        ],
        'handler'  => \App\Sockets\TestTcpSocket::class,
    ],
],
```

For TCP socket, events `onConnect` and `onClose` will be blocked when `dispatch_mode` of Swoole is set to `1/3`. So if you want to unblock these two events please set `dispatch_mode` to `2/4/5`.

```PHP
'swoole' => [
    //...
    'dispatch_mode' => 2,
    //...
];
```

3. Test.

- TCP: `telnet 127.0.0.1 5291`

- UDP: `echo "Hello LaravelS" > /dev/udp/127.0.0.1/5291`

## Important notices

- [Known compatible issues](https://github.com/hhxsv5/laravel-s/blob/master/KnownCompatibleIssues.md)

- Get all info of request from `Illuminate\Http\Request` Object, compatible with $_SERVER/$_ENV/$_GET/$_POST/$_FILES/$_COOKIE/$_REQUEST, `CANNOT USE` $_SESSION.

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

- Respond by `Illuminate\Http\Response` Object, compatible with echo/vardump()/print_r()，`CANNOT USE` functions like header()/setcookie()/http_response_code().

```PHP
public function json()
{
    return response()->json(['time' => time()])->header('header1', 'value1')->withCookie('c1', 'v1');
}
```

- The various `singleton connections` will be `memory resident`, recommend to enable `persistent connection`.
1. Database connection, it `will` reconnect automatically `immediately` after disconnect.
```PHP
// config/database.php
//...
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
    //...
],
//...
```
2. Redis connection, it `won't` reconnect automatically `immediately` after disconnect, and will throw an exception about lost connection, reconnect next time. You need to make sure that `SELECT DB` correctly before operating Redis every time.
```PHP
// config/database.php
'redis' => [
        'default' => [
            'host'       => env('REDIS_HOST', 'localhost'),
            'password'   => env('REDIS_PASSWORD', null),
            'port'       => env('REDIS_PORT', 6379),
            'database'   => 0,
            'persistent' => true, // Enable persistent connection
        ],
    ],
//...
```

- `global`, `static` variables which you declared are need to destroy(reset) manually.

- Infinitely appending element into `static`/`global` variable will lead to memory leak.

```PHP
// Some class
class Test
{
    public static $array = [];
    public static $string = '';
}

// Controller
public function test(Request $req)
{
    // Memory leak
    Test::$array[] = $req->input('param1');
    Test::$string .= $req->input('param2');
}
```

## Todo list

1. Connection pool for MySQL/Redis.

2. Wrap coroutine clients for MySQL/Redis/Http.

## Alternatives

- [swooletw/laravel-swoole](https://github.com/swooletw/laravel-swoole)

## License

[MIT](https://github.com/hhxsv5/laravel-s/blob/master/LICENSE)
