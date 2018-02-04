<?php

namespace Hhxsv5\LaravelS\Clients\Coroutine;


use Hhxsv5\LaravelS\Clients\Base;
use Swoole\Coroutine\MySQL as SwooleMySQLClient;
use Swoole\Coroutine;

class MySQL extends Base
{
    public function __construct()
    {
        $this->cli = new SwooleMySQLClient();
    }

    public function __call($name, $arguments)
    {
        return Coroutine::call_user_func_array([$this->cli, $name], $arguments);
    }
}