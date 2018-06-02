<?php

namespace Hhxsv5\LaravelS\Swoole\Socket;

abstract class WebSocket implements PortInterface, WebSocketInterface
{
    /**
     * @var  \swoole_server_port
     */
    protected $swoolePort;

    public function __construct(\swoole_server_port $port)
    {
        $this->swoolePort = $port;
    }
}