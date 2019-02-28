<?php

namespace Hhxsv5\LaravelS\Swoole\Socket;

use Swoole\Http\Server;

interface UdpInterface
{
    public function onPacket(Server $server, $data, array $clientInfo);
}