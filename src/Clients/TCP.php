<?php

namespace Hhxsv5\LaravelS\Clients;

class TCP extends Socket
{
    public function __construct($syncType = false)
    {
        parent::__construct(\SWOOLE_TCP, $syncType);
    }
}