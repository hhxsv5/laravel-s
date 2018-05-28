<?php

namespace Hhxsv5\LaravelS\Swoole\Socket;

abstract class UdpSocket implements PortInterface, UdpInterface
{
    /**
     * @var  \swoole_server_port
     */
    protected $swoolePort;

    public function __construct(\swoole_server_port $port)
    {
        $this->swoolePort = $port;
    }

    abstract public function onPacket(\swoole_server $server, $data, array $clientInfo);
}