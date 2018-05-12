<?php

namespace Hhxsv5\LaravelS\Swoole\Socket;

interface UdpInterface
{
    public function onPacket(\swoole_server $server, $data, array $clientInfo);
}