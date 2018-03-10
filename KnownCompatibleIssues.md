# Known compatibility issues

## Use package [jenssegers/agent](https://github.com/jenssegers/agent)
> [Listen System Event](https://github.com/hhxsv5/laravel-s/blob/master/README.md#system-events)

```PHP
// Reset UserAgent
\Event::listen('laravels.received_request', function (\Illuminate\Http\Request $req) {
    $this->app->agent->setUserAgent($req->server->get('HTTP_USER_AGENT'));
});
```
