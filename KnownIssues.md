# Known issues

## Class swoole does not exist
- In `LaravelS`, `Swoole` is `Http Server` started in `cli` mode, replacing `FPM`.
- Delivering a task, triggering an asynchronous event will call `app('swoole')` and get the `Swoole\http\server` instance from the `Laravel container`. This instance is injected into the container only when `LaravelS` is started.
- So, once you leave the `LaravelS`, due to the cross-process, you will be `unable` to successfully call `app('swoole')`:
    - The code that runs in various `command line` modes, such as the Artisan command line and the PHP script command line.
    - Run the code under `FPM`/`Apache PHP Module`, view SAPI `Log::info('PHP SAPI', [php_sapi_name()]);`.

## Use package [encore/laravel-admin](https://github.com/z-song/laravel-admin)
> Modify `config/laravels.php` and add` LaravelAdminCleaner` in `cleaners`.

```php
'cleaners' => [
    Hhxsv5\LaravelS\Illuminate\Cleaners\LaravelAdminCleaner::class,
],
```

## Use package [jenssegers/agent](https://github.com/jenssegers/agent)
> [Listen System Event](https://github.com/hhxsv5/laravel-s/blob/master/README.md#system-events)

```php
// Reset Agent
\Event::listen('laravels.received_request', function (\Illuminate\Http\Request $req, $app) {
    $app->agent->setHttpHeaders($req->server->all());
    $app->agent->setUserAgent();
});
```

## Use package [barryvdh/laravel-debugbar](https://github.com/barryvdh/laravel-debugbar)
> Not support `cli` mode officially, you need to add the environment variable `APP_RUNNING_IN_CONSOLE` to be non-cli`, but there may be some other issues.

Add environment variable `APP_RUNNING_IN_CONSOLE=false` to `.env`.

## Use package [the-control-group/voyager](https://github.com/the-control-group/voyager)
> `voyager` dependencies [arrilot/laravel-widgets](https://github.com/arrilot/laravel-widgets), where `WidgetGroupCollection` is a singleton, [appending widget](https://github.com/Arrilot/laravel-widgets/blob/master/src/WidgetGroup.php#L270) will cause them to repeat the display, you need to reset the singleton by re-registering the ServiceProvider.

```php
// config/laravels.php
'register_providers' => [
    Arrilot\Widgets\ServiceProvider::class,
],
```

## Use package [overtrue/wechat](https://github.com/overtrue/wechat)
> The asynchronous notification callback will be failing, because `$app['request']->getContent()` is empty, give it a value.

```php
public function notify(Request $request)
{
    $app = $this->getPayment();//Get payment instance
    $app['request'] = $request;//Add this line to the original code and assign the current request instance to $app['request']
    $response = $app->handlePaidNotify(function ($message, $fail) use($id) {
        //...
    });
    return $response;
}
```
## Use package [laracasts/flash](https://github.com/laracasts/flash)
> Flash messages are held in memory all the time. Appending to `$messages` when call flash() every time, leads to the multiple messages. There are two solutions.

1.Reset `$messages` by middleware `app('flash')->clear();`.

2.Re-register `FlashServiceProvider` after handling request, Refer [register_providers](https://github.com/hhxsv5/laravel-s/blob/master/Settings.md).

## Use package [laravel/telescope](https://github.com/laravel/telescope)
> Because Swoole is running in `cli` mode, `RequestWatcher` does not recognize the ignored route properly.

Solution:

1.Add environment variable `APP_RUNNING_IN_CONSOLE=false` to `.env`;

2.Modify code.

```php
// Edit file `app/Providers/EventServiceProvider.php`, add the following code into method `boot`
// use Laravel\Telescope\Telescope;
// use Illuminate\Support\Facades\Event;
Event::listen('laravels.received_request', function ($request, $app) {
    $reflection = new \ReflectionClass(Telescope::class);
    $handlingApprovedRequest = $reflection->getMethod('handlingApprovedRequest');
    $handlingApprovedRequest->setAccessible(true);
    $handlingApprovedRequest->invoke(null, $app) ? Telescope::startRecording() : Telescope::stopRecording();
});
```

## Singleton controller

- Laravel 5.3+ controller is bound to `Route` under `Router`, and `Router` is a singleton, controller will only be constructed `once`, so you cannot initialize `request-level data` in the constructor, the following shows you the `wrong` usage.

```php
namespace App\Http\Controllers;
class TestController extends Controller
{
    protected $userId;
    public function __construct()
    {
        // Wrong usage: Since the controller is only constructed once and then resident in memory, $userId will only be assigned once, and subsequent requests will be misread before requesting $userId
        $this->userId = session('userId');
    }
    public function testAction()
    {
        // read $this->userId;
    }
}
```

- Two solutions (choose one)

1.Avoid initializing `request-level` data in the constructor, which should be read in the concrete `Action`. This coding style is more reasonable, it is recommended to do so.

```bash
# List all properties of all controllers related your routes.
php artisan laravels:list-properties
```

```php
namespace App\Http\Controllers;
class TestController extends Controller
{
    protected function getUserId()
    {
        return session('userId');
    }
    public function testAction()
    {
        // call $this->getUserId() to read $userId
    }
}
```

2.Use the `automatic destruction controller` mechanism provided by `LaravelS`.

```php
// config/laravels.php
// Set enable to true and exclude_list to [], which means that all controllers are automatically destroyed.
'destroy_controllers'      => [
    'enable'        => true, // Enable automatic destruction controller
    'excluded_list' => [
        //\App\Http\Controllers\TestController::class, // The excluded list of destroyed controller classes
    ],
],
```

## Cannot call these functions

- `flush`/`ob_flush`/`ob_end_flush`/`ob_implicit_flush`: `swoole_http_response` does not support `flush`.

- `dd()`/`exit()`/`die()`: will lead to Worker/Task/Process quit right now, suggest jump out function call stack by throwing exception.

- `header()`/`setcookie()`/`http_response_code()`: Make HTTP response by Laravel/Lumen `Response` only in LaravelS underlying.

## Cannot use these global variables

- $_GET/$_POST/$_FILES/$_COOKIE/$_REQUEST/$_SESSION/$GLOBALS, $_ENV is `readable`, $_SERVER is `partial readable`.

## Size restriction

- The max size of `GET` request's header is `8KB`, limited by `Swoole`, the big `Cookie` will lead to parse Cookie fail.

- The max size of `POST` data/file is limited by `Swoole` [package_max_length](https://www.swoole.co.uk/docs/modules/swoole-server/configuration), default `2M`.

- The max size of the file upload is limited by [memory_limit](https://www.php.net/manual/en/ini.core.php#ini.memory-limit), default `128M`.

## Inotify reached the watchers limit
> `Warning: inotify_add_watch(): The user limit on the total number of inotify watches was reached`

- Inotify limit is default `8192` for most `Linux`, but the amount of actual project may be more than it, then lead to watch fail.

- Increase the amount of inotify watchers to `524288`: `echo fs.inotify.max_user_watches=524288 | sudo tee -a /etc/sysctl.conf && sudo sysctl -p`, note: you need to enable `privileged` for `Docker`.

## include/require与(include/require)_once
> See Laruence's blog [Do NOT USE (include/require)_once](http://www.laruence.com/2012/09/12/2765.html)

- To include the files about `class`/`interface`/`trait`/`function`, sugguest to use (include/require)_once. In other cases, use include/require.

- In the multi-process mode, the child process inherits the parent process resource. Once the parent process includes a file that needs to be executed, the child process will directly return true when it uses require_once(), causing the file to fail to execute. Now, you need to use include/require.

## If `Swoole < 1.9.17`
> After enabling `handle_static`, static resource files will be handled by `LaravelS`. Due to the PHP environment, `MimeTypeGuesser` may not correctly recognize `MimeType`. For example, Javascript and CSS files will be recognized as `text/plain`.

Solutions:

1.Upgrade Swoole to `1.9.17+`.

2.Register a custom MIME guesser.

```php
// MyGuessMimeType.php
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;
class MyGuessMimeType implements MimeTypeGuesserInterface
{
    protected static $map = [
        'js'  => 'application/javascript',
        'css' => 'text/css',
    ];
    public function guess($path)
    {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        if (strlen($ext) > 0) {
            return Arr::get(self::$map, $ext);
        } else {
            return null;
        }
    }
}
```

```php
// AppServiceProvider.php
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
public function boot()
{
    MimeTypeGuesser::getInstance()->register(new MyGuessMimeType());
}
```

