<?php

namespace Hhxsv5\LaravelS\Swoole\Socket;

interface SocketInterface
{
    public function __construct(\swoole_server_port $port);
    public function onConnect(\swoole_server $server, $fd, $reactorId);
    public function onClose(\swoole_server $server, $fd, $reactorId);
    public function onReceive(\swoole_server $server, $fd, $reactorId, $data);
    public function onPacket(\swoole_server $server, $data,  $clientInfo);
}