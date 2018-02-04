<?php

namespace Hhxsv5\LaravelS\Clients;

class Socket extends Base
{
    public function __construct($sockType, $syncType = false)
    {
        $this->cli = new \swoole_client($sockType, $syncType);
    }
}