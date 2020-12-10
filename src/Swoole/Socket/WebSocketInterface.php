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

    // It would be conflicted with 'onOpen' if you implement this method as you need to handle 'onOpen' inside this method
    // Our decision is to make it to be an optional case
    //public function onHandShake(Request $request,Response $response);
}