<?php

namespace Hhxsv5\LaravelS\Swoole\Socket;

interface PortInterface
{
    public function __construct(\swoole_server_port $port);
}