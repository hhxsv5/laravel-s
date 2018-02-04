<?php

namespace Hhxsv5\LaravelS\Clients\Coroutine;


use Hhxsv5\LaravelS\Clients\BaseClient;
use Swoole\Coroutine\MySQL as SwooleMySQLClient;
use Swoole\Coroutine;

class MySQL extends BaseClient
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