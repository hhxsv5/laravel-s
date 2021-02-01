# LaravelS 配置项

## listen_ip
> `string` 监听的IP，监听本机`127.0.0.1`(IPv4) `::1`(IPv6)，监听所有地址 `0.0.0.0`(IPv4) `::`(IPv6)， 默认`127.0.0.1`。

## listen_port
> `int` 监听的端口，如果端口小于1024则需要`root`权限，默认 `5200`。

## socket_type
> 默认`SWOOLE_SOCK_TCP`。通常情况下，无需关心这个配置。若需Nginx代理至`UnixSocket Stream`文件，则需修改为`SWOOLE_SOCK_UNIX_STREAM`，此时`listen_ip`则是`UnixSocket Stream`文件的路径。

## server
> `string` 当通过LaravelS响应数据时，设置HTTP头部`Server`的值，若为空则不设置，默认 `LaravelS`。

## handle_static
> `bool` 是否开启LaravelS处理静态资源(要求 `Swoole >= 1.7.21`，若`Swoole >= 1.9.17`则由Swoole自己处理)，默认`false`，建议Nginx处理静态资源，LaravelS仅处理动态资源。静态资源的默认路径为`base_path('public')`，可通过修改`swoole.document_root`变更。

## laravel_base_path
> `string` `Laravel/Lumen`的基础路径，默认`base_path()`，可用于配置`符号链接`。

## inotify_reload.enable
> `bool` 是否开启`Inotify Reload`，用于当修改代码后实时Reload所有worker进程，依赖库[inotify](http://pecl.php.net/package/inotify)，通过命令`php --ri inotify`检查是否可用，默认`false`，`建议仅开发环境开启`，[修改监听数上限](https://github.com/hhxsv5/laravel-s/blob/master/KnownIssues-CN.md#inotify%E7%9B%91%E5%90%AC%E6%96%87%E4%BB%B6%E6%95%B0%E8%BE%BE%E5%88%B0%E4%B8%8A%E9%99%90)。
 
## inotify_reload.watch_path
> `string` `Inotify` 监控的文件路径，默认有`base_path()`。

## inotify_reload.file_types
> `array` `Inotify` 监控的文件类型，默认有`.php`。

## inotify_reload.excluded_dirs
> `array` `Inotify` 监控时需要排除(或忽略)的目录，默认`[]`，示例：`[base_path('vendor')]`。

## inotify_reload.log
> `bool` 是否输出Reload的日志，默认`true`。

## event_handlers
> `array` 配置`Swoole`的事件回调函数，key-value格式，key为事件名，value为实现了事件处理接口的类，参考[示例](https://github.com/hhxsv5/laravel-s/blob/master/README-CN.md#%E9%85%8D%E7%BD%AEswoole%E7%9A%84%E4%BA%8B%E4%BB%B6%E5%9B%9E%E8%B0%83%E5%87%BD%E6%95%B0)。

## websocket.enable
> `bool` 是否启用WebSocket服务器。启用后WebSocket服务器监听的IP和端口与Http服务器相同，默认`false`。

## websocket.handler
> `string` WebSocket逻辑处理的类名，需实现接口`WebSocketHandlerInterface`，参考[示例](https://github.com/hhxsv5/laravel-s/blob/master/README-CN.md#%E5%90%AF%E7%94%A8websocket%E6%9C%8D%E5%8A%A1%E5%99%A8)。

## sockets
> `array` 配置`TCP/UDP`套接字列表，参考[示例](https://github.com/hhxsv5/laravel-s/blob/master/README-CN.md#%E5%A4%9A%E7%AB%AF%E5%8F%A3%E6%B7%B7%E5%90%88%E5%8D%8F%E8%AE%AE)。

## processes
> `array` 配置自定义进程列表，参考[示例](https://github.com/hhxsv5/laravel-s/blob/master/README-CN.md#%E8%87%AA%E5%AE%9A%E4%B9%89%E8%BF%9B%E7%A8%8B)。

## timer
> `array` 配置毫秒定时器，参考[示例](https://github.com/hhxsv5/laravel-s/blob/master/README-CN.md#%E6%AF%AB%E7%A7%92%E7%BA%A7%E5%AE%9A%E6%97%B6%E4%BB%BB%E5%8A%A1)。

## swoole_tables
> `array` 定义的`swoole_table`列表，参考[示例](https://github.com/hhxsv5/laravel-s/blob/master/README-CN.md#%E4%BD%BF%E7%94%A8swoole_table)。

## cleaners
> `array` `每次请求`的清理器列表，用于清理一些残留的全局变量、单例对象、静态属性，避免多次请求间数据污染。这些清理器类必须实现接口`Hhxsv5\LaravelS\Illuminate\Cleaners\CleanerInterface`。清理的顺序与数组的顺序保持一致。[这些清理器](https://github.com/hhxsv5/laravel-s/blob/master/src/Illuminate/CleanerManager.php#L31)默认已启用，无需再配置。

```php
// 如果你的项目中使用到了Session、Authentication、Passport，需配置如下清理器
'cleaners' => [
    Hhxsv5\LaravelS\Illuminate\Cleaners\SessionCleaner::class,
    Hhxsv5\LaravelS\Illuminate\Cleaners\AuthCleaner::class,
],
```

```php
// 如果你的项目中使用到了包"tymon/jwt-auth"，需配置如下清理器
'cleaners' => [
    Hhxsv5\LaravelS\Illuminate\Cleaners\SessionCleaner::class,
    Hhxsv5\LaravelS\Illuminate\Cleaners\AuthCleaner::class,
    Hhxsv5\LaravelS\Illuminate\Cleaners\JWTCleaner::class,
],
```

```php
// 如果你的项目中使用到了包"spatie/laravel-menu"，需配置如下清理器
'cleaners' => [
    Hhxsv5\LaravelS\Illuminate\Cleaners\MenuCleaner::class,
],
```

```php
// 如果你的项目中使用到了包"encore/laravel-admin"，需配置如下清理器
'cleaners' => [
    Hhxsv5\LaravelS\Illuminate\Cleaners\SessionCleaner::class,
    Hhxsv5\LaravelS\Illuminate\Cleaners\AuthCleaner::class,
    Hhxsv5\LaravelS\Illuminate\Cleaners\LaravelAdminCleaner::class,
],
```

```php
// 如果你的项目中使用到了包"jqhph/dcat-admin"
'cleaners' => [
    Hhxsv5\LaravelS\Illuminate\Cleaners\SessionCleaner::class,
    Hhxsv5\LaravelS\Illuminate\Cleaners\AuthCleaner::class,
    Hhxsv5\LaravelS\Illuminate\Cleaners\DcatAdminCleaner::class,
],
```

## register_providers
> `array` `每次请求`需要重新注册的`Service Provider`列表，若存在`boot()`方法，会自动执行。一般用于清理`注册了单例的ServiceProvider`。

```php
//...
'register_providers' => [
    \Xxx\Yyy\XxxServiceProvider::class,
],
//...
```

## destroy_controllers
> `array` 每次请求后自动销毁控制器，解决单例控制器的问题，参考[示例](https://github.com/hhxsv5/laravel-s/blob/master/KnownIssues-CN.md#%E5%8D%95%E4%BE%8B%E6%8E%A7%E5%88%B6%E5%99%A8)。

## swoole
> `array` Swoole的`原始`配置项，请参考[Swoole服务器配置项](https://wiki.swoole.com/#/server/setting)。