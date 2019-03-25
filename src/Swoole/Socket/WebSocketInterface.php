<?php

namespace Hhxsv5\LaravelS\Swoole\Socket;

use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

interface WebSocketInterface
{
    public function onOpen(Server $server, Request $request);

    public function onMessage(Server $server, Frame $frame);

    public function onClose(Server $server, $fd, $reactorId);
}