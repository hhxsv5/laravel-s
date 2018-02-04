<?php

namespace Hhxsv5\LaravelS\Clients;

class Socket extends BaseClient
{
    public function __construct($sockType, $syncType = false)
    {
        $this->cli = new \swoole_client($sockType, $syncType);
    }
}