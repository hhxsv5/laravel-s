# LaravelS Settings

- `listen_ip`: `string` The listening ip, like local address `127.0.0.1`(IPv4) `::1`(IPv6), all addresses `0.0.0.0`(IPv4) `::`(IPv6), default `127.0.0.1`.

- `listen_port`: `int` The listening port, need `root` permission if port less than `1024`, default `5200`.

- `socket_type`: `int` Default `SWOOLE_SOCK_TCP`. Usually, you don’t need to care about it. Unless you want Nginx to proxy to the `UnixSocket Stream` file, you need to modify it to `SWOOLE_SOCK_UNIX_STREAM`, and `listen_ip` is the path of `UnixSocket Stream` file.

- `enable_coroutine_runtime`: `bool` Whether enable [runtime coroutine](https://wiki.swoole.com/wiki/page/965.html), require `Swoole>=4.1.0`, default `false`.

- `server`: `string` Set HTTP header `Server` when respond by LaravelS, default `LaravelS`.

- `handle_static`: `bool` Whether handle the static resource by LaravelS(Require `Swoole >= 1.7.21`, Handle by Swoole if `Swoole >= 1.9.17`), default `false`, Suggest that Nginx handles the statics and LaravelS handles the dynamics. The default path of static resource is `base_path('public')`, you can modify `swoole.document_root` to change it.

- `laravel_base_path`: `string` The basic path of `Laravel/Lumen`, default `base_path()`, be used for `symbolic link`.

- `inotify_reload.enable`: `bool` Whether enable the `Inotify Reload` to reload all worker processes when your code is modified, depend on [inotify](http://pecl.php.net/package/inotify), use `php --ri inotify` to check whether the available. default `false`, `recommend to enable in development environment only`, change [Watchers Limit](https://github.com/hhxsv5/laravel-s/blob/master/KnownIssues.md#inotify-reached-the-watchers-limit).

- `inotify_reload.watch_path`：`string` The file path that `Inotify` watches, default `base_path()`.

- `inotify_reload.file_types`: `array` The file types that `Inotify` watches, default `['.php']`.

- `inotify_reload.excluded_dirs`: `array` The excluded/ignored directories that `Inotify` watches, default `[]`, eg: `[base_path('vendor')]`.

- `inotify_reload.log`: `bool` Whether output the reload log, default `true`.

- `websocket.enable`: `bool` Whether enable WebSocket Server. The Listening address of WebSocket Sever is the same as Http Server, default `false`.

- `websocket.handler`: `string` The class name for WebSocket handler, needs to implement interface `WebSocketHandlerInterface`, refer [Demo](https://github.com/hhxsv5/laravel-s/blob/master/README.md#enable-websocket-server).

- `sockets`: `array` The socket list for TCP/UDP, refer [Demo](https://github.com/hhxsv5/laravel-s/blob/master/README.md#multi-port-mixed-protocol).

- `processes`: `array` The custom process list, refer [Demo](https://github.com/hhxsv5/laravel-s/blob/master/README.md#custom-process).

- `events`: `array` The customized asynchronous event list of listener binding, refer [Demo](https://github.com/hhxsv5/laravel-s/blob/master/README.md#customized-asynchronous-events).

- `swoole_tables`: `array` The defined of `swoole_table` list, refer [Demo](https://github.com/hhxsv5/laravel-s/blob/master/README.md#use-swoole_table).

- `register_providers`: `array` The `Service Provider` list, will be re-registered `every request`, and run method `boot()` if it exists. Usually, be used to clear the `Service Provider` which registers `Singleton` instances.
    ```php
    //...
    'register_providers' => [
        //eg: re-register ServiceProvider of jwt
        \Tymon\JWTAuth\Providers\LaravelServiceProvider::class,
    ],
    //...
    ```

- `swoole`: `array` Swoole's `original` configuration items, refer [Swoole Configuration](https://www.swoole.co.uk/docs/modules/swoole-server/configuration).