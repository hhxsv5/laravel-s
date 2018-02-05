<?php

namespace Hhxsv5\LaravelS\Clients\Coroutine;

class UDP extends Socket
{
    public function __construct()
    {
        parent::__construct(\SWOOLE_UDP);
    }
}