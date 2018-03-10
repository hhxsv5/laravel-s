# 已知的兼容性问题

## 使用包 [jenssegers/agent](https://github.com/jenssegers/agent)

```PHP
// 重置UserAgent
\Event::listen('laravels.received_request', function (\Illuminate\Http\Request $req) {
    $this->app->agent->setUserAgent($req->server->get('HTTP_USER_AGENT'));
});
```