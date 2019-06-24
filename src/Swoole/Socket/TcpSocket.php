<?php

namespace Hhxsv5\LaravelS\Swoole\Socket;

use Swoole\Server;
use Swoole\Server\Port;

abstract class TcpSocket implements PortInterface, TcpInterface
{
    protected $swoolePort;

    public function __construct(Port $port)
    {
        $this->swoolePort = $port;
    }

    public function onConnect(Server $server, $fd, $reactorId)
    {
    }

    public function onClose(Server $server, $fd, $reactorId)
    {
    }

    public function onBufferFull(Server $server, $fd)
    {
    }

    public function onBufferEmpty(Server $server, $fd)
    {
    }

    abstract public function onReceive(Server $server, $fd, $reactorId, $data);
}