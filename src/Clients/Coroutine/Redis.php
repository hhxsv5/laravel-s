<?php

namespace Hhxsv5\LaravelS\Clients\Coroutine;

use Hhxsv5\LaravelS\Clients\Base;
use Swoole\Coroutine\Redis as SwooleRedisClient;
use Swoole\Coroutine;

class Redis extends Base
{
    public function __construct()
    {
        $this->cli = new SwooleRedisClient();
    }

    public function __call($name, $arguments)
    {
        return Coroutine::call_user_func_array([$this->cli, $name], $arguments);
    }

}