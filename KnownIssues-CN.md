# 常见问题

## Class swoole does not exist
- 在`LaravelS`中，`Swoole`是以`cli`模式启动的`Http Server`，替代了`FPM`。
- 投递任务、触发异步事件都会调用`app('swoole')`，从`Laravel容器`中获取`Swoole\http\server`实例。只有在`LaravelS`启动时，才会注入这个实例到容器中。
- 所以，一旦脱离`LaravelS`，由于跨进程，以下情况，你将`无法`成功调用`app('swoole')`：
    - 以各种`命令行`方式运行的代码，例如Artisan命令行、PHP脚本命令行；
    - 运行在`FPM`/`Apache PHP Module`下的代码，查看SAPI `Log::info('PHP SAPI', [php_sapi_name()]);`。

## 使用包 [encore/laravel-admin](https://github.com/z-song/laravel-admin)
> 修改`config/laravels.php`，在`cleaners`中增加`LaravelAdminCleaner`。

```php
'cleaners' => [
    Hhxsv5\LaravelS\Illuminate\Cleaners\LaravelAdminCleaner::class,
],
```

## 使用包 [jenssegers/agent](https://github.com/jenssegers/agent)
> [监听系统事件](https://github.com/hhxsv5/laravel-s/blob/master/README-CN.md#%E7%B3%BB%E7%BB%9F%E4%BA%8B%E4%BB%B6)

```php
// 重置Agent
\Event::listen('laravels.received_request', function (\Illuminate\Http\Request $req, $app) {
    $app->agent->setHttpHeaders($req->server->all());
    $app->agent->setUserAgent();
});
```

## 使用包 [barryvdh/laravel-debugbar](https://github.com/barryvdh/laravel-debugbar)
> 官方不支持`cli`模式，需通过修改环境变量`APP_RUNNING_IN_CONSOLE`为非`cli`，但启用后不排除会有其他问题。

`.env`中增加环境变量`APP_RUNNING_IN_CONSOLE=false`。

## 使用包 [the-control-group/voyager](https://github.com/the-control-group/voyager)
> `voyager`依赖包[arrilot/laravel-widgets](https://github.com/arrilot/laravel-widgets)，而其中`WidgetGroupCollection`是单例，[追加Widget](https://github.com/arrilot/laravel-widgets/blob/master/src/WidgetGroup.php#L270)会造成它们重复展示，通过重新注册ServiceProvider来重置此单例。

```php
// config/laravels.php
'register_providers' => [
    Arrilot\Widgets\ServiceProvider::class,
],
```

## 使用包 [overtrue/wechat](https://github.com/overtrue/wechat)
> easywechat包会出现异步通知回调失败的问题，原因是`$app['request']->getContent()`是空的，给其赋值即可。

```php
//回调通知
public function notify(Request $request)
{
    $app = $this->getPayment();//获取支付实例
    $app['request'] = $request;//在原有代码添加这一行，将当前Request赋值给$app['request']
    $response = $app->handlePaidNotify(function ($message, $fail) use($id) {
        //...
    });
    return $response;
}
```


## 使用包 [laracasts/flash](https://github.com/laracasts/flash)
> 常驻内存后，每次调用flash()会追加消息提醒，导致叠加展示消息提醒。有以下两个方案。

1.通过中间件在每次请求`处理前`或`处理后`重置$messages `app('flash')->clear();`。

2.每次请求处理后重新注册`FlashServiceProvider`，配置[register_providers](https://github.com/hhxsv5/laravel-s/blob/master/Settings-CN.md)。

## 使用包 [laravel/telescope](https://github.com/laravel/telescope)
> 因Swoole运行在`cli`模式，导致`RequestWatcher`不能正常识别忽略的路由。

解决方案：

1.`.env`中增加环境变量`APP_RUNNING_IN_CONSOLE=false`；

2.修改代码。

```php
// 修改`app/Providers/EventServiceProvider.php`, 添加下面监听代码到boot方法中
// use Laravel\Telescope\Telescope;
// use Illuminate\Support\Facades\Event;
Event::listen('laravels.received_request', function ($request, $app) {
    $reflection = new \ReflectionClass(Telescope::class);
    $handlingApprovedRequest = $reflection->getMethod('handlingApprovedRequest');
    $handlingApprovedRequest->setAccessible(true);
    $handlingApprovedRequest->invoke(null, $app) ? Telescope::startRecording() : Telescope::stopRecording();
});
```

## 单例控制器

- Laravel 5.3+ 控制器是被绑定在`Router`下的`Route`中，而`Router`是单例，控制器只会被构造`一次`，所以不能在构造方法中初始化`请求级数据`，下面展示`错误的用法`。

```php
namespace App\Http\Controllers;
class TestController extends Controller
{
    protected $userId;
    public function __construct()
    {
        // 错误的用法：因控制器只被构造一次，然后常驻于内存，所以$userId只会被赋值一次，后续请求会误读取之前请求$userId
        $this->userId = session('userId');
    }
    public function testAction()
    {
        // 读取$this->userId;
    }
}
```

- 两种解决方法(二选一)

1.避免在构造函数中初始化`请求级`的数据，应在具体`Action`中读取，这样编码风格更合理，建议这样写。

```bash
# 列出你的路由中所有关联的控制器的所有属性
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
        // 通过调用$this->getUserId()读取$userId
    }
}
```

2.使用`LaravelS`提供的`自动销毁控制器`机制。

```php
// config/laravels.php
// 将enable置为true、excluded_list置为[]，则表示自动销毁所有控制器
'destroy_controllers'      => [
    'enable'        => true, // 启用自动销毁控制器
    'excluded_list' => [
        //\App\Http\Controllers\TestController::class, // 排除销毁的控制器类列表
    ],
],
```

## 不能使用这些函数

- `flush`/`ob_flush`/`ob_end_flush`/`ob_implicit_flush`：`swoole_http_response`不支持`flush`。

- `dd()`/`exit()`/`die()`: 将导致Worker/Task/Process进程立即退出，建议通过抛异常跳出函数调用栈，[Swoole文档](https://wiki.swoole.com/wiki/page/501.html)。

- `header()`/`setcookie()`/`http_response_code()`：HTTP响应只能通过Laravel/Lumen的`Response`对象。

## 不能使用的全局变量

- $_GET、$_POST、$_FILES、$_COOKIE、$_REQUEST、$_SESSION、$GLOBALS，$_ENV是`可读`的，$_SERVER是`部分可读`的。

## 大小限制

- `Swoole`限制了`GET`请求头的最大尺寸为`8KB`，建议`Cookie`的不要太大，不然Cookie可能解析失败。

- `POST`数据或文件上传的大小受`Swoole`配置[`package_max_length`](https://wiki.swoole.com/wiki/page/301.html)限制，默认上限`2M`。

- 文件上传的大小受到了[memory_limit](https://www.php.net/manual/zh/ini.core.php#ini.memory-limit)的限制，默认`128M`。

## Inotify监听文件数达到上限
> `Warning: inotify_add_watch(): The user limit on the total number of inotify watches was reached`

- `Linux`中`Inotify`监听文件数默认上限一般是`8192`，实际项目的文件数+目录树很可能超过此上限，进而导致后续的监听失败。

- 增加此上限到`524288`：`echo fs.inotify.max_user_watches=524288 | sudo tee -a /etc/sysctl.conf && sudo sysctl -p`，注意`Docker`时需设置启用`privileged`。

## 注意include/require与(include/require)_once
> 看看鸟哥这篇文章[再一次, 不要使用(include/require)_once](http://www.laruence.com/2012/09/12/2765.html)

- 引入`类`、`接口`、`trait`、`函数`时使用(include/require)_once，其他情况使用include/require。

- 在多进程模式下，子进程会继承父进程资源，一旦父进程引入了某个需要被执行的文件，子进程再次`require_once()`时会直接返回`true`，导致该文件执行失败。此时，你应该使用include/require。


## 对于`Swoole < 1.9.17`的环境
> 开启`handle_static`后，静态资源文件将由`LaravelS`组件处理。由于PHP环境的原因，可能会导致`MimeTypeGuesser`无法正确识别`MimeType`，比如会Javascript与CSS文件会被识别为`text/plain`。

解决方案：

1.升级Swoole到`1.9.17+`

2.注册自定义MIME猜测器

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

