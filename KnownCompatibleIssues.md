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

- `exit()`/`die()`: will lead to Worker/Task/Process quit right now, suggest jump out function call stack by throwing exception.

- `header()`/`setcookie()`/`http_response_code()`: Make HTTP response by `swoole_http_response` only in LaravelS underlying.

## Cannot use these global variables

- `$_SESSION`