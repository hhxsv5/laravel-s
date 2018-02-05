<?php

namespace Hhxsv5\LaravelS\Clients\Coroutine;

use Hhxsv5\LaravelS\Clients\Base;
use Swoole\Coroutine\Client as SwooleCoroutineClient;
use Swoole\Coroutine;

class Socket extends Base
{
    public function __construct($sockType)
    {
        $this->cli = new SwooleCoroutineClient($sockType);
    }

    public function __call($name, $arguments)
    {
        return Coroutine::call_user_func_array([$this->cli, $name], $arguments);
    }
}