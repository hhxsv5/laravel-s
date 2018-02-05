<?php

namespace Hhxsv5\LaravelS\Clients\Coroutine;

class TCP extends Socket
{
    public function __construct()
    {
        parent::__construct(\SWOOLE_TCP);
    }
}