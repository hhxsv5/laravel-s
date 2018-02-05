<?php

namespace Hhxsv5\LaravelS\Clients;

class UDP extends Socket
{
    public function __construct($syncType = false)
    {
        parent::__construct(\SWOOLE_UDP, $syncType);
    }
}