# laravel-s
LaravelS: Speed up Laravel/Lumen with Swoole, 'S' means Swoole, Speed, High performance.

## Requirements

- PHP >= 5.5.9

- Swoole >= 1.7.7

- Laravel/Lumen >= 5.1

## Install

1. Require package 
```Bash
composer require "hhxsv5/laravel-s:~1.0" -vvv
```

2. Add service provider in `config/app.php` file
```PHP
'providers' => [
    //...
    Hhxsv5\LaravelS\Illuminate\LaravelSServiceProvider::class,
],
```

3. Publish
```PHP
php artisan vendor:publish --provider="Hhxsv5\LaravelS\Illuminate\LaravelSServiceProvider"
```

4. Change config/laravels.php: listen_ip, listen_port ...

## Run Demo

```Bash
php artisan laravels {action : start|stop|reload}
```

## License

[MIT](https://github.com/hhxsv5/laravel-s/blob/master/LICENSE)
