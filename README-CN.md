# LaravelS - 站在巨人的肩膀上
> 通过Swoole来加速 Laravel/Lumen，其中的S代表Swoole，速度，高性能。

[![Latest Stable Version](https://poser.pugx.org/hhxsv5/laravel-s/v/stable.svg)](https://packagist.org/packages/hhxsv5/laravel-s)
[![Total Downloads](https://poser.pugx.org/hhxsv5/laravel-s/downloads.svg)](https://packagist.org/packages/hhxsv5/laravel-s)
[![Latest Unstable Version](https://poser.pugx.org/hhxsv5/laravel-s/v/unstable.svg)](https://packagist.org/packages/hhxsv5/laravel-s)
[![License](https://poser.pugx.org/hhxsv5/laravel-s/license.svg)](https://packagist.org/packages/hhxsv5/laravel-s)
[![Build Status](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/badges/build.png?b=master)](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/?branch=master)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)
<!-- [![Code Coverage](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/hhxsv5/laravel-s/?branch=master) -->

## 特性

- 高性能的Swoole

- 内置Http服务器

- 常驻内存

- 平滑重启

- 同时支持Laravel与Lumen，兼容主流版本

- 简单，开箱即用

## 要求

| 依赖 | 说明 |
| -------- | -------- |
| [PHP](https://secure.php.net/manual/zh/install.php) | `>= 5.5.9` |
| [Swoole](https://www.swoole.com/) | `>= 1.7.14` `推荐最新的稳定版` |
| [Laravel](https://laravel.com/)/[Lumen](https://lumen.laravel.com/) | `>= 5.1` |
| gzip[可选] | [zlib](https://zlib.net/), Ubuntu/Debian: `sudo apt-get install zlibc zlib1g zlib1g-dev`, CentOS: `sudo yum install zlib` |

## 安装

1.通过composer安装([packagist](https://packagist.org/packages/hhxsv5/laravel-s))

```Bash
composer require "hhxsv5/laravel-s:~1.0" -vvv
```

2.添加service provider

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

3.发布配置文件
```Bash
php artisan laravels:publish
```

`特别情况`: 你不需要手动加载配置`laravels.php`，LaravelS底层已自动加载。
```PHP
// 不必手动加载，但加载了也不会有问题
$app->configure('laravels');
```

4.修改配置`config/laravels.php`：监听的IP，监听的端口，[Swoole的配置](https://wiki.swoole.com/wiki/page/274.html)等等。

## 运行
> `php artisan laravels {start|stop|restart|reload|publish}`

| 命令 | 说明 |
| --------- | --------- |
| `start` | 启动LaravelS |
| `stop` | 停止LaravelS |
| `restart` | 重启LaravelS |
| `reload` | 重新加载所有worker进程，这些worker进程内包含你的业务代码和框架(Laravel/Lumen)代码，不会重启master/manger进程 |
| `publish` | 发布配置文件到`config/laravels.php` |

## 与Nginx配合使用

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

## 监听事件

- `laravels.received_request` 将`swoole_http_request`转成`Illuminate\Http\Request`后，在Laravel内核处理请求前。

```PHP
// 修改`app/Providers/EventServiceProvider.php`, 添加下面监听代码到boot方法中
$events->listen('laravels.received_request', function (\Illuminate\Http\Request $req) {
    $req->query->set('get_key', 'hhxsv5');// 修改querystring
    $req->request->set('post_key', 'hhxsv5'); // 修改post body
});
```

- `laravels.generated_response` 在Laravel内核处理完请求后，将`Illuminate\Http\Response`转成`swoole_http_response`之前(下一步将响应给客户端)。

```PHP
$events->listen('laravels.generated_response', function (\Illuminate\Http\Request $req, \Symfony\Component\HttpFoundation\Response $rsp) {
    $rsp->header('header-key', 'hhxsv5');// 修改header
});
```

## 注意事项

- 只能通过`Illuminate\Http\Request`对象来获取请求信息，不能使用超全局变量，像$GLOBALS，$_SERVER，$_GET，$_POST，$_FILES，$_COOKIE，$_SESSION，$_REQUEST，$_ENV。

```PHP
public function form(\Illuminate\Http\Request $request)
{
    $name = $request->input('name');
    $all = $request->all();
    $sessionId = $request->cookie('sessionId');
    $photo = $request->file('photo');
    //...
}
```

- 推荐通过`Illuminate\Http\Response`对象返回响应信息，兼容echo、vardump()、print_r()，不能通过函数像exit()，die()，header()，setcookie()，http_response_code()。

```PHP
public function json()
{
    return response()->json(['time' => time()])->header('header1', 'value1')->withCookie('c1', 'v1');
}
```

- 你声明的全局、静态变量必须手动清理掉。

- 无限追加元素到静态或全局变量中，将导致内存爆满。

```PHP
class Test
{
    public static $array = [];
    public static $string = '';
}
public function test(Request $req)
{
    // 内存爆满
    Test::$array[] = $req->input('param1');
    Test::$string .= $req->input('param2');
}
```

## License

[MIT](https://github.com/hhxsv5/laravel-s/blob/master/LICENSE)
