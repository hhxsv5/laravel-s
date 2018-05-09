<?php

namespace Hhxsv5\LaravelS\Swoole\Socket;

interface UdpInterface
{
    public function onReceive(\swoole_server $server, $fd, $reactorId, $data);

    public function onPacket(\swoole_server $server, $data, array $clientInfo);
}