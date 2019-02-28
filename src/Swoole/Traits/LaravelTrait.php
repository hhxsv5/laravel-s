<?php

namespace Hhxsv5\LaravelS\Swoole\Traits;

use Hhxsv5\LaravelS\Illuminate\Laravel;
use Swoole\Http\Server;

trait LaravelTrait
{
    protected function initLaravel(array $conf, Server $swoole)
    {
        $laravel = new Laravel($conf);
        $laravel->prepareLaravel();
        $laravel->bindSwoole($swoole);
        return $laravel;
    }
}