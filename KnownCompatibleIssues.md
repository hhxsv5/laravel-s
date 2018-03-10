# Known compatibility issues

## use package [jenssegers/agent](https://github.com/jenssegers/agent)

```PHP
// Reset UserAgent
\Event::listen('laravels.received_request', function (\Illuminate\Http\Request $req) {
    $this->app->agent->setUserAgent($req->server->get('HTTP_USER_AGENT'));
});
```
