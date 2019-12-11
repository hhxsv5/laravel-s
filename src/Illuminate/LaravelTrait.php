<?php

namespace Hhxsv5\LaravelS\Illuminate;

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