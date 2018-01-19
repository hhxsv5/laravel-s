# laravel-s
Speed up laravel with swoole, 'S' means Swoole, Speed, High performance.

## Requirements

- PHP >= 5.5.9

- Swoole >= 1.7.7

- Laravel >= 5.1

## Install

```Bash
composer require "hhxsv5/laravel-s:~1.0.0" -vvv
```

## Run Demo

```PHP
//run in laravel console!
$svrConf = ['ip' => '0.0.0.0', 'port' => 8011];
$laravelConf = ['rootPath' => base_path()];
$s = \Hhxsv5\LaravelS\LaravelS::getInstance($svrConf, $laravelConf);
$s->run();
```