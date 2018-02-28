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

**[English Documentation](README.md)**

## 特性

- 高性能的Swoole

- 内置Http服务器

- 常驻内存

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
| Inotify[可选的] | [inotify](http://pecl.php.net/package/inotify)，用于实时地reload worker进程，检查本机`inotify`是否可用 *php --ri inotify* |

## 安装

1.通过[Composer](https://getcomposer.org/)安装([packagist](https://packagist.org/packages/hhxsv5/laravel-s))

```Bash
# 在你的Laravel/Lumen项目的根目录下执行
composer require "hhxsv5/laravel-s:~1.0" -vvv
# 确保你的composer.lock文件是在版本控制中
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
php artisan laravels publish
```

`特别情况`: 你不需要手动加载配置`laravels.php`，LaravelS底层已自动加载。
```PHP
// 不必手动加载，但加载了也不会有问题
$app->configure('laravels');
```

4.修改配置`config/laravels.php`：监听的IP、端口等，请参考[配置项](Settings-CN.md)。

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
        proxy_set_header Host $host;
        proxy_pass http://laravels;
    }
}
```

## 与Apache配合使用

```Apache
<VirtualHost *:443>
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

## 监听事件
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
$events->listen('laravels.generated_response', function (\Illuminate\Http\Request $req, \Symfony\Component\HttpFoundation\Response $rsp) {
    $rsp->headers->set('header-key', 'hhxsv5');// 修改header
});
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

## 待办事项

1. 针对MySQL/Redis的连接池。

2. 包装MySQL/Redis/Http的协程客户端。

3. 针对Swoole `2.1+` 自动的协程支持。

4. 支持`inotify`，实现修改代码后自动重启或重新加载。

## License

[MIT](https://github.com/hhxsv5/laravel-s/blob/master/LICENSE)
