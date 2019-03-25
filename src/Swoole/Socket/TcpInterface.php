<?php

namespace Hhxsv5\LaravelS\Swoole\Socket;

use Swoole\Server;

interface TcpInterface
{
    public function onConnect(Server $server, $fd, $reactorId);

    public function onClose(Server $server, $fd, $reactorId);

    public function onReceive(Server $server, $fd, $reactorId, $data);

    public function onBufferFull(Server $server, $fd);

    public function onBufferEmpty(Server $server, $fd);
}