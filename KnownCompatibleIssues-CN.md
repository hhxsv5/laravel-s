# 已知的兼容性问题

## 使用包 [jenssegers/agent](https://github.com/jenssegers/agent)
> [监听系统事件](https://github.com/hhxsv5/laravel-s/blob/master/README-CN.md#%E7%B3%BB%E7%BB%9F%E4%BA%8B%E4%BB%B6)

```PHP
// 重置UserAgent
\Event::listen('laravels.received_request', function (\Illuminate\Http\Request $req) {
    $this->app->agent->setHttpHeaders($req->server->all());
    $this->app->agent->setUserAgent();
});
```