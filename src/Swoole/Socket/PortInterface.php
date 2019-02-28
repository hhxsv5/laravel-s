<?php

namespace Hhxsv5\LaravelS\Swoole\Socket;

use Swoole\Server\Port;

interface PortInterface
{
    public function __construct(Port $port);
}