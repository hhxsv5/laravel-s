# LaravelS
Speed up Laravel/Lumen by Swoole, 'S' means Swoole, Speed, High performance.

[![Latest Stable Version](https://poser.pugx.org/hhxsv5/laravel-s/v/stable.svg)](https://packagist.org/packages/hhxsv5/laravel-s)
[![Total Downloads](https://poser.pugx.org/hhxsv5/laravel-s/downloads.svg)](https://packagist.org/packages/hhxsv5/laravel-s)
[![Latest Unstable Version](https://poser.pugx.org/hhxsv5/laravel-s/v/unstable.svg)](https://packagist.org/packages/hhxsv5/laravel-s)
[![License](https://poser.pugx.org/hhxsv5/laravel-s/license.svg)](https://packagist.org/packages/hhxsv5/laravel-s)
[![Build Status](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/badges/build.png?b=master)](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/?branch=master)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)
<!-- [![Code Coverage](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/?branch=master) -->

## Features

- High performance Swoole

- Built-in Http Server

- Memory resident

- Gracefully reload

- Simple & Out of the box

## Requirements

| Dependency | Requirement |
| -------- | -------- |
| [PHP](https://secure.php.net/manual/en/install.php) | `>= 5.5.9` |
| [Swoole](https://www.swoole.com/) | `>= 1.7.14` `The New The Better` |
| [Laravel](https://laravel.com/)/[Lumen](https://lumen.laravel.com/) | `>= 5.1` |
| GZIP[Optional] | [zlib](https://zlib.net/), Ubuntu/Debian: `sudo apt-get install zlibc zlib1g zlib1g-dev`, CentOS: `sudo yum install zlib` |

## Install

1.Require package via Composer([packagist](https://packagist.org/packages/hhxsv5/laravel-s))

```Bash
composer require "hhxsv5/laravel-s:~1.0" -vvv
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
php artisan laravels:publish
```

`Special for Lumen`: you `DO NOT` need to load this configuration manually in `bootstrap/app.php` file. LaravelS will load it automatically.
```PHP
// Unnecessary to call configure()
$app->configure('laravels');
```

4.Change `config/laravels.php`: listen_ip, listen_port, [swoole's settings](https://wiki.swoole.com/wiki/page/274.html) ...

## Run Demo
> `php artisan laravels {start|stop|restart|reload|publish}`

| Command | Description |
| --------- | --------- |
| `start` | Start LaravelS |
| `stop` | Stop LaravelS |
| `restart` | Restart LaravelS |
| `reload` | Reload all worker process(Contain your business & Laravel/Lumen codes), exclude master/manger process |
| `publish` | Publish configuration file `laravels.php` of LaravelS into folder `config` |

## Cooperate with Nginx

```Nginx
upstream laravels {
    server 192.168.0.1:5200 weight=5 max_fails=3 fail_timeout=30s;
    #server 192.168.0.2:5200 weight=3 max_fails=3 fail_timeout=30s;
    #server 192.168.0.3:5200 backup;
}
server {
    listen 80;
    server_name laravels.com;
    root /xxx/laravel-s-test/public;
    access_log /docker/log/nginx/$server_name.access.log  main;

    location / {
        proxy_http_version 1.1;
        proxy_set_header Connection "keep-alive";
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header Host $host;
        proxy_pass http://laravels;
    }
}
```

## Listen Events

- `laravels.received_request` After LaravelS parsed `swoole_http_request` to `Illuminate\Http\Request`, before Laravel's Kernel handles this request.

```PHP
// Edit file `app/Providers/EventServiceProvider.php`, add the following code into method `boot`
$events->listen('laravels.received_request', function (\Illuminate\Http\Request $req) {
    $req->query->set('get_key', 'hhxsv5');// Change query of request
    $req->request->set('post_key', 'hhxsv5'); // Change post of request
    \Log::info('Received Request', [$req->getRequestUri(), $req->query->all(), $req->request->all()]);
});
```

- `laravels.generated_response` After Laravel's Kernel handled the request, before LaravelS parses `Illuminate\Http\Response` to `swoole_http_response`.

```PHP
$events->listen('laravels.generated_response', function (\Illuminate\Http\Request $req, \Symfony\Component\HttpFoundation\Response $rsp) {
    $rsp->header('header-key', 'hhxsv5');// Change header of response
    \Log::info('Generated Response', [$req->getRequestUri(), $rsp->getContent()]);
});
```

## Important Notices

- `global`, `static` variables which you declared are need to destroy(reset) manually.

- Never use `superglobal` variables, like $GLOBALS, $_SERVER, $_GET, $_POST, $_FILES, $_COOKIE, $_SESSION, $_REQUEST, $_ENV

- Infinitely appending element into `static`/`global` variable will lead to memory leak.
```PHP
// Some class
class Test
{
    public static $array = [];
    public static $string = '';
}
// In Controller
public function test(Request $req)
{
    // Memory leak
    Test::$array[] = $req->input('param1');
    Test::$string .= $req->input('param2');
}
```

## License

[MIT](https://github.com/hhxsv5/laravel-s/blob/master/LICENSE)
