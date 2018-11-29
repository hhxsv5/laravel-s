# LaravelS 配置项

- `listen_ip`：`string` 监听的IP，监听本机`127.0.0.1`(IPv4) `::1`(IPv6)，监听所有地址 `0.0.0.0`(IPv4) `::`(IPv6)， 默认`127.0.0.1`。

- `listen_port`：`int` 监听的端口，如果端口小于1024则需要`root`权限，默认 `5200`。

- `socket_type`：默认`SWOOLE_SOCK_TCP`。通常情况下，无需关心这个配置。若需Nginx代理至`UnixSocket Stream`文件，则需修改为`SWOOLE_SOCK_UNIX_STREAM`，此时`listen_ip`则是`UnixSocket Stream`文件的路径。

- `enable_coroutine_runtime`：`bool` 是否启用[运行时协程](https://wiki.swoole.com/wiki/page/965.html)，需`Swoole>=4.1.0`，默认`false`。

- `server`：`string` 当通过LaravelS响应数据时，设置HTTP头部`Server`的值，若为空则不设置，默认 `LaravelS`。

- `handle_static`：`bool` 是否开启LaravelS处理静态资源(要求 `Swoole >= 1.7.21`，若`Swoole >= 1.9.17`则由Swoole自己处理)，默认`false`，建议Nginx处理静态资源，LaravelS仅处理动态资源。静态资源的默认路径为`base_path('public')`，可通过修改`swoole.document_root`变更。

- `laravel_base_path`：`string` `Laravel/Lumen`的基础路径，默认`base_path()`，可用于配置`符号链接`。

- `inotify_reload.enable`：`bool` 是否开启`Inotify Reload`，用于当修改代码后实时Reload所有worker进程，依赖库[inotify](http://pecl.php.net/package/inotify)，通过命令`php --ri inotify`检查是否可用，默认`false`，`建议仅开发环境开启`，[修改监听数上限](https://github.com/hhxsv5/laravel-s/blob/master/KnownIssues-CN.md#inotify%E7%9B%91%E5%90%AC%E6%96%87%E4%BB%B6%E6%95%B0%E8%BE%BE%E5%88%B0%E4%B8%8A%E9%99%90)。
 
- `inotify_reload.watch_path`：`string` `Inotify` 监控的文件路径，默认有`base_path()`。

- `inotify_reload.file_types`：`array` `Inotify` 监控的文件类型，默认有`.php`。

- `inotify_reload.excluded_dirs`: `array` `Inotify` 监控时需要排除(或忽略)的目录，默认`[]`，示例：`[base_path('vendor')]`。

- `inotify_reload.log`：`bool` 是否输出Reload的日志，默认`true`。

- `websocket.enable`：`bool` 是否启用WebSocket服务器。启用后WebSocket服务器监听的IP和端口与Http服务器相同，默认`false`。

- `websocket.handler`：`string` WebSocket逻辑处理的类名，需实现接口`WebSocketHandlerInterface`，参考[示例](https://github.com/hhxsv5/laravel-s/blob/master/README-CN.md#%E5%90%AF%E7%94%A8websocket%E6%9C%8D%E5%8A%A1%E5%99%A8)。

- `sockets`：`array` 配置`TCP/UDP`套接字列表，参考[示例](https://github.com/hhxsv5/laravel-s/blob/master/README-CN.md#%E5%A4%9A%E7%AB%AF%E5%8F%A3%E6%B7%B7%E5%90%88%E5%8D%8F%E8%AE%AE)。

- `processes`：`array` 配置自定义进程列表，参考[示例](https://github.com/hhxsv5/laravel-s/blob/master/README-CN.md#%E8%87%AA%E5%AE%9A%E4%B9%89%E8%BF%9B%E7%A8%8B)。

- `events`：`array` 自定义的异步事件和监听的绑定列表，参考[示例](https://github.com/hhxsv5/laravel-s/blob/master/README-CN.md#%E8%87%AA%E5%AE%9A%E4%B9%89%E7%9A%84%E5%BC%82%E6%AD%A5%E4%BA%8B%E4%BB%B6)。

- `swoole_tables`：`array` 定义的`swoole_table`列表，参考[示例](https://github.com/hhxsv5/laravel-s/blob/master/README-CN.md#%E4%BD%BF%E7%94%A8swoole_table)。

- `register_providers`：`array` `每次请求`需要重新注册的`Service Provider`列表，若存在`boot()`方法，会自动执行。一般用于清理`注册了单例的ServiceProvider`。
    ```php
    //...
    'register_providers' => [
        //例如：重新注册jwt的ServiceProvider
        \Tymon\JWTAuth\Providers\LaravelServiceProvider::class,
    ],
    //...
    ```

- `swoole`：`array` Swoole的`原始`配置项，请参考[Swoole配置项](https://wiki.swoole.com/wiki/page/274.html)。