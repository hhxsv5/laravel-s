# Known compatibility issues

## Use package [jenssegers/agent](https://github.com/jenssegers/agent)
> [Listen System Event](https://github.com/hhxsv5/laravel-s/blob/master/README.md#system-events)

```PHP
// Reset Agent
\Event::listen('laravels.received_request', function (\Illuminate\Http\Request $req, $app) {
    $app->agent->setHttpHeaders($req->server->all());
    $app->agent->setUserAgent();
});
```

## Cannot call these functions

- `flush`/`ob_flush`/`ob_end_flush`/`ob_implicit_flush`: `swoole_http_response` does not support `flush`.

- `dd()`/`exit()`/`die()`: will lead to Worker/Task/Process quit right now, suggest jump out function call stack by throwing exception.

- `header()`/`setcookie()`/`http_response_code()`: Make HTTP response by Laravel/Lumen `Response` only in LaravelS underlying.

## Cannot use these global variables

- `$_SESSION`

## Size restriction

- The max size of `GET` request's header is `8KB`, restricted by `Swoole`, the big `Cookie` will lead to parse `$_COOKIE` fail.

- The max size of `POST` data/file is restricted by `Swoole` [`package_max_length`](https://www.swoole.co.uk/docs/modules/swoole-server/configuration), default `2M`.

## Inotify reached the watchers limit
> `Warning: inotify_add_watch(): The user limit on the total number of inotify watches was reached`

- Inotify limit is default `8192` for most `Linux`, but the amount of actual project may be more than it, then lead to watch fail.

- Increase the amount of inotify watchers to `524288`: `echo fs.inotify.max_user_watches=524288 | sudo tee -a /etc/sysctl.conf && sudo sysctl -p`, note: you need to enable `privileged` for `Docker`.