# 已知的兼容性问题

## 使用包 [jenssegers/agent](https://github.com/jenssegers/agent)
> [监听系统事件](https://github.com/hhxsv5/laravel-s/blob/master/README-CN.md#%E7%B3%BB%E7%BB%9F%E4%BA%8B%E4%BB%B6)

```PHP
// 重置Agent
\Event::listen('laravels.received_request', function (\Illuminate\Http\Request $req, $app) {
    $app->agent->setHttpHeaders($req->server->all());
    $app->agent->setUserAgent();
});
```

## 不能使用这些函数

- `flush`/`ob_flush`/`ob_end_flush`/`ob_implicit_flush`：`swoole_http_response`不支持`flush`。

- `dd()`/`exit()`/`die()`: 将导致Worker/Task/Process进程立即退出，建议通过抛异常跳出函数调用栈，[Swoole文档](https://wiki.swoole.com/wiki/page/501.html)。

- `header()`/`setcookie()`/`http_response_code()`：HTTP响应只能通过Laravel/Lumen的`Response`对象。

## 不能使用的全局变量

- `$_SESSION`

## 大小限制

- `Swoole`限制了`GET`请求头的最大尺寸为`8KB`，建议`Cookie`的不要太大，不然`$_COOKIE`可能解析失败。

- `POST`数据或文件上传的最大尺寸受`Swoole`配置[`package_max_length`](https://wiki.swoole.com/wiki/page/301.html)影响，默认上限`2M`。

## Inotify监听文件数达到上限
> `Warning: inotify_add_watch(): The user limit on the total number of inotify watches was reached`

- `Linux`中`Inotify`监听文件数默认上限一般是`8192`，实际项目的文件数+目录树很可能超过此上限，进而导致后续的监听失败。

- 增加此上限到`524288`：`echo fs.inotify.max_user_watches=524288 | sudo tee -a /etc/sysctl.conf && sudo sysctl -p`，注意`Docker`时需设置启用`privileged`。