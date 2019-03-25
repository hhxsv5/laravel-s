<?php

namespace Hhxsv5\LaravelS\Swoole\Socket;

use Swoole\Server;
use Swoole\Server\Port;

abstract class UdpSocket implements PortInterface, UdpInterface
{
    protected $swoolePort;

    public function __construct(Port $port)
    {
        $this->swoolePort = $port;
    }

    abstract public function onPacket(Server $server, $data, array $clientInfo);
}