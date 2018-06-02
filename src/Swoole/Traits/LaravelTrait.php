<?php

namespace Hhxsv5\LaravelS\Swoole\Traits;

use Hhxsv5\LaravelS\Illuminate\Laravel;

trait LaravelTrait
{
    protected function initLaravel(array $conf, \swoole_server $swoole)
    {
        $laravel = new Laravel($conf);
        $laravel->prepareLaravel();
        $laravel->bindSwoole($swoole);
        return $laravel;
    }
}