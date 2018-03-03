<?php

namespace Hhxsv5\LaravelS\Swoole\Websocket;

interface Handler
{
    public function onOpen(\swoole_websocket_server $server, \swoole_http_request $request);

    public function onMessage(\swoole_websocket_server $server, \swoole_websocket_frame $frame);

    public function onClose(\swoole_websocket_server $server, $fd, $reactorId);
}