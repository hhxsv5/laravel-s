# LaravelS Settings

## listen_ip
> `string` The listening ip, like local address `127.0.0.1`(IPv4) `::1`(IPv6), all addresses `0.0.0.0`(IPv4) `::`(IPv6), default `127.0.0.1`.

## listen_port
> `int` The listening port, need `root` permission if port less than `1024`, default `5200`.

## socket_type
> `int` Default `SWOOLE_SOCK_TCP`. Usually, you don’t need to care about it. Unless you want Nginx to proxy to the `UnixSocket Stream` file, you need to modify it to `SWOOLE_SOCK_UNIX_STREAM`, and `listen_ip` is the path of `UnixSocket Stream` file.

## server
> `string` Set HTTP header `Server` when respond by LaravelS, default `LaravelS`.

## handle_static
> `bool` Whether handle the static resource by LaravelS(Require `Swoole >= 1.7.21`, Handle by Swoole if `Swoole >= 1.9.17`), default `false`, Suggest that Nginx handles the statics and LaravelS handles the dynamics. The default path of static resource is `base_path('public')`, you can modify `swoole.document_root` to change it.

## laravel_base_path
> `string` The basic path of `Laravel/Lumen`, default `base_path()`, be used for `symbolic link`.

## inotify_reload.enable
> `bool` Whether enable the `Inotify Reload` to reload all worker processes when your code is modified, depend on [inotify](http://pecl.php.net/package/inotify), use `php --ri inotify` to check whether the available. default `false`, `recommend to enable in development environment only`, change [Watchers Limit](https://github.com/hhxsv5/laravel-s/blob/master/KnownIssues.md#inotify-reached-the-watchers-limit).

## inotify_reload.watch_path
> `string` The file path that `Inotify` watches, default `base_path()`.

## inotify_reload.file_types
> `array` The file types that `Inotify` watches, default `['.php']`.

## inotify_reload.excluded_dirs
> `array` The excluded/ignored directories that `Inotify` watches, default `[]`, eg: `[base_path('vendor')]`.

## inotify_reload.log
> `bool` Whether output the reload log, default `true`.

## event_handlers
> `array` Configure the event callback function of `Swoole`, key-value format, key is the event name, and value is the class that implements the event processing interface, refer [Demo](https://github.com/hhxsv5/laravel-s/blob/master/README.md#configuring-the-event-callback-function-of-swoole).

## websocket.enable
> `bool` Whether enable WebSocket Server. The Listening address of WebSocket Sever is the same as Http Server, default `false`.

## websocket.handler
> `string` The class name for WebSocket handler, needs to implement interface `WebSocketHandlerInterface`, refer [Demo](https://github.com/hhxsv5/laravel-s/blob/master/README.md#enable-websocket-server).

## sockets
> `array` The socket list for TCP/UDP, refer [Demo](https://github.com/hhxsv5/laravel-s/blob/master/README.md#multi-port-mixed-protocol).

## processes
> `array` The custom process list, refer [Demo](https://github.com/hhxsv5/laravel-s/blob/master/README.md#custom-process).

## timer
> `array` The millisecond timer, refer [Demo](https://github.com/hhxsv5/laravel-s/blob/master/README.md#millisecond-cron-job).

## swoole_tables
> `array` The defined of `swoole_table` list, refer [Demo](https://github.com/hhxsv5/laravel-s/blob/master/README.md#use-swoole_table).

## cleaners
> `array` The list of cleaners for `each request` is used to clean up some residual global variables, singleton objects, and static properties to avoid data pollution between requests, these classes must implement interface `Hhxsv5\LaravelS\Illuminate\Cleaners\CleanerInterface`. The order of cleanup is consistent with the order of the arrays. [These cleaners](https://github.com/hhxsv5/laravel-s/blob/master/src/Illuminate/CleanerManager.php#L31) enabled by default, and do not need to be configured.

```php
// Need to configure the following cleaners if you use the session/authentication/passport in your project
'cleaners' => [
    Hhxsv5\LaravelS\Illuminate\Cleaners\SessionCleaner::class,
    Hhxsv5\LaravelS\Illuminate\Cleaners\AuthCleaner::class,
],
```

```php
// Need to configure the following cleaners if you use the package "tymon/jwt-auth" in your project
'cleaners' => [
    Hhxsv5\LaravelS\Illuminate\Cleaners\SessionCleaner::class,
    Hhxsv5\LaravelS\Illuminate\Cleaners\AuthCleaner::class,
    Hhxsv5\LaravelS\Illuminate\Cleaners\JWTCleaner::class,
],
```

```php
// Need to configure the following cleaners if you use the package "spatie/laravel-menu" in your project
'cleaners' => [
    Hhxsv5\LaravelS\Illuminate\Cleaners\MenuCleaner::class,
],
```

```php
// Need to configure the following cleaners if you use the package "encore/laravel-admin" in your project
'cleaners' => [
    Hhxsv5\LaravelS\Illuminate\Cleaners\SessionCleaner::class,
    Hhxsv5\LaravelS\Illuminate\Cleaners\AuthCleaner::class,
    Hhxsv5\LaravelS\Illuminate\Cleaners\LaravelAdminCleaner::class,
],
```

```php
// Need to configure the following cleaners if you use the package "jqhph/dcat-admin" in your project
'cleaners' => [
    Hhxsv5\LaravelS\Illuminate\Cleaners\SessionCleaner::class,
    Hhxsv5\LaravelS\Illuminate\Cleaners\AuthCleaner::class,
    Hhxsv5\LaravelS\Illuminate\Cleaners\DcatAdminCleaner::class,
],
```

## register_providers
> `array` The `Service Provider` list, will be re-registered `each request`, and run method `boot()` if it exists. Usually, be used to clear the `Service Provider` which registers `Singleton` instances.

```php
//...
'register_providers' => [
    \Xxx\Yyy\XxxServiceProvider::class,
],
//...
```

## destroy_controllers
> `array` Automatically destroy the controllers after each request to solve the problem of the singleton controllers, refer [Demo](https://github.com/hhxsv5/laravel-s/blob/master/KnownIssues.md#singleton-controller).

## swoole
> `array` Swoole's `original` configuration items, refer [Swoole Server Configuration](https://www.swoole.co.uk/docs/modules/swoole-server/configuration).