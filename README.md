# LaravelS - standing on the shoulders of giants
> 🚀 Speed up Laravel/Lumen by Swoole, 'S' means Swoole, Speed, High performance.

[![Latest Stable Version](https://poser.pugx.org/hhxsv5/laravel-s/v/stable.svg)](https://packagist.org/packages/hhxsv5/laravel-s)
[![Total Downloads](https://poser.pugx.org/hhxsv5/laravel-s/downloads.svg)](https://packagist.org/packages/hhxsv5/laravel-s)
[![Latest Unstable Version](https://poser.pugx.org/hhxsv5/laravel-s/v/unstable.svg)](https://packagist.org/packages/hhxsv5/laravel-s)
[![License](https://poser.pugx.org/hhxsv5/laravel-s/license.svg)](https://github.com/hhxsv5/laravel-s/blob/master/LICENSE)
[![Build Status](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/badges/build.png?b=master)](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/?branch=master)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)
<!-- [![Code Coverage](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/?branch=master) -->

**[中文文档](README-CN.md)**

## Features

- High performance Swoole

- Built-in Http Server

- Memory resident

- Asynchronous event listening base on Task

- Elegant delivery for asynchronous task

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

1.Require package via [Composer](https://getcomposer.org/)([packagist](https://packagist.org/packages/hhxsv5/laravel-s))

```Bash
# Run in the root path of your Laravel/Lumen project.
composer require "hhxsv5/laravel-s:~1.0" -vvv
# Make sure that your composer.lock file is under the VCS
```

2.Add service provider

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

3.Publish Configuration
```Bash
php artisan laravels publish
```

`Special for Lumen`: you `DO NOT` need to load this configuration manually in `bootstrap/app.php` file. LaravelS will load it automatically.
```PHP
// Unnecessary to call configure()
$app->configure('laravels');
```

4.Change `config/laravels.php`: listen_ip, listen_port, refer [Settings](Settings.md).

## Run Demo
> `php artisan laravels {start|stop|restart|reload|publish}`

| Command | Description |
| --------- | --------- |
| `start` | Start LaravelS, list the processes by *ps -ef&#124;grep laravels* |
| `stop` | Stop LaravelS |
| `restart` | Restart LaravelS |
| `reload` | Reload all worker processes(Contain your business & Laravel/Lumen codes), exclude master/manger process |
| `publish` | Publish configuration file `laravels.php` of LaravelS into folder `config` of your project |

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
    
    # Nginx handles the static resources, LaravelS handles the dynamic resource.
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

## Listen Events

### System Events
> Usually, you can reset/destroy some `global/static` variables, or change the current `Request/Response` object.

- `laravels.received_request` After LaravelS parsed `swoole_http_request` to `Illuminate\Http\Request`, before Laravel's Kernel handles this request.

```PHP
// Edit file `app/Providers/EventServiceProvider.php`, add the following code into method `boot`
// If no variable $exents, you can also call \Event::listen(). 
$events->listen('laravels.received_request', function (\Illuminate\Http\Request $req) {
    $req->query->set('get_key', 'hhxsv5');// Change query of request
    $req->request->set('post_key', 'hhxsv5'); // Change post of request
});
```

- `laravels.generated_response` After Laravel's Kernel handled the request, before LaravelS parses `Illuminate\Http\Response` to `swoole_http_response`.

```PHP
$events->listen('laravels.generated_response', function (\Illuminate\Http\Request $req, \Symfony\Component\HttpFoundation\Response $rsp) {
    $rsp->headers->set('header-key', 'hhxsv5');// Change header of response
});
```

### Customized Asynchronous Events
> The performance of listener processing is influenced by number of Swoole task process, you need to set [task_worker_num](https://www.swoole.co.uk/docs/modules/swoole-server/configuration) appropriately.

1.Create event class
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

2.Create listener class
```PHP
use Hhxsv5\LaravelS\Swoole\Task\Event;
use Hhxsv5\LaravelS\Swoole\Task\Listener;

class TestListener1 extends Listener
{
    public function handle(Event $event)
    {
        // $data = $event->getData();
        sleep(2);// Simulate the slow codes
        // throw new \Exception('an exception'); // all exceptions will be ignored
    }
}
```

3.Bind event & listeners
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

4.Fire event
```PHP
// Create instance of event and fire it, "fire" is asynchronous.
$success = Event::fire(new TestEvent('event data'));
var_dump($success);// Return true if sucess, otherwise false
```

## Elegant delivery for asynchronous task
> The performance of task processing is influenced by number of Swoole task process, you need to set [task_worker_num](https://www.swoole.co.uk/docs/modules/swoole-server/configuration) appropriately.

1.Create task class
```PHP
use Hhxsv5\LaravelS\Swoole\Task\Task;

class TestTask extends Task
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        sleep(2);// Simulate the slow codes
        // throw new \Exception('an exception'); // all exceptions will be ignored
    }
}
```

2.Deliver task
```PHP
// Create instance of TestTask and deliver it, "deliver" is asynchronous.
$ret = Task::deliver(new TestTask('task data'));
var_dump($ret);// Return true if sucess, otherwise false
```

## Get the instance of swoole_http_server in your project

```PHP
/**
* @var \swoole_http_server
*/
$swoole = app('swoole');// Singleton
var_dump($swoole->stats());
```

## Important Notices

- Get all info of request from `Illuminate\Http\Request` Object, compatible with $_SERVER/$_GET/$_POST/$_FILES/$_COOKIE/$_REQUEST, `CANNOT USE` $_SESSION, $_ENV.

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

## License

[MIT](https://github.com/hhxsv5/laravel-s/blob/master/LICENSE)
