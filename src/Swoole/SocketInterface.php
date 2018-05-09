<?php

namespace Hhxsv5\LaravelS\Swoole;

interface SocketInterface
{
    public function onConnect(\swoole_server $server, $fd, $reactorId);
    public function onClose(\swoole_server $server, $fd, $reactorId);
    public function onReceive(\swoole_server $server, $fd, $reactorId, $data);
    public function onPacket(\swoole_server $server, $data,  $clientInfo);
}