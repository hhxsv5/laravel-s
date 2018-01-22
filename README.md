# laravel-s
LaravelS: Speed up Laravel/Lumen with Swoole, 'S' means Swoole, Speed, High performance.

## Requirements

- PHP >= 5.5.9

- Swoole >= 1.7.7

- Laravel/Lumen >= 5.1

## Install

```Bash
//require package
composer require "hhxsv5/laravel-s:~1.0" -vvv
//publish config
php artisan vendor:publish --provider="Hhxsv5\LaravelS\Illuminate\LaravelSServiceProvider"
//change config/laravels.php
//listen_ip, lisent_port ...
```

## Run Demo

```Bash
php artisan laravels {action : start|stop|reload}
```

## License

[MIT](https://github.com/hhxsv5/laravel-s/blob/master/LICENSE)
