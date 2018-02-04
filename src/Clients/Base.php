<?php

namespace Hhxsv5\LaravelS\Clients;

class BaseClient
{
    protected $cli;

    public function __get($name)
    {
        if (isset($this->cli->$name)) {
            return $this->cli->$name;
        }
        return null;
    }

    public function __call($name, $arguments)
    {
        if (is_callable('\Swoole\Coroutine::call_user_func_array')) {
            return \Swoole\Coroutine::call_user_func_array([$this->cli, $name], $arguments);
        }
        return call_user_func_array([$this->cli, $name], $arguments);
    }
}