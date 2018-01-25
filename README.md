# LaravelS
Speed up Laravel/Lumen by Swoole, 'S' means Swoole, Speed, High performance.

## Features

- High performance Swoole

- Built-in Http Server

- Memory resident

- Gracefully reload

- Simple & Out of the box

## Requirements

| Dependency | Requirement |
| -------- | -------- |
| [PHP](https://secure.php.net/manual/en/install.php) | `>= 5.5.9` |
| [Swoole](https://www.swoole.com/) | `>= 1.7.7` |
| [Laravel](https://laravel.com/)/[Lumen](https://lumen.laravel.com/) | `>= 5.1` |

## Install

1.Require package 
```Bash
composer require "hhxsv5/laravel-s:~1.0" -vvv
```

2.Add service provider in `config/app.php` file
```PHP
'providers' => [
    //...
    Hhxsv5\LaravelS\Illuminate\LaravelSServiceProvider::class,
],
```

3.Publish
```PHP
php artisan vendor:publish --provider="Hhxsv5\LaravelS\Illuminate\LaravelSServiceProvider"
```

4.Change config/laravels.php: listen_ip, listen_port, [swoole's settings](https://wiki.swoole.com/wiki/page/274.html) ...

## Run Demo

```Bash
php artisan laravels {start|stop|restart|reload}
```

## Important Notices

- `global`, `static` variables which you declared are need to destroy(reset) manually.

- Never use `superglobal` variables, like $GLOBALS, $_SERVER, $_GET, $_POST, $_FILES, $_COOKIE, $_SESSION, $_REQUEST, $_ENV

- Infinitely appending element into `static`/`global` variable will lead to memory leak.
```PHP
// Some class
class Test
{
    public static $array = [];
    public static $string = '';
}
// In Controller
public function test(Request $req)
{
    // Memory leak
    Test::$array[] = $req->input('param1');
    Test::$string .= $req->input('param2');
}
```

## TODO

- gzip

## License

[MIT](https://github.com/hhxsv5/laravel-s/blob/master/LICENSE)
