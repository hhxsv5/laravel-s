<?php

namespace Hhxsv5\LaravelS\Swoole\Socket;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

interface WebSocketInterface
{
    /**
     * The function is executed when a WebSocket connection is established and going into the handshake stage.
     * The built-in default handshake protocol is Sec-WebSocket-Version: 13. You can override the default handshake protocol by implementing this function.
     * After the onHandShake event is set, the onOpen event will not be triggered, and onOpen can be called manually
     * This function is optional to create a WebSocket server.
     * @see https://www.swoole.co.uk/docs/modules/swoole-websocket-server-on-handshake
     * @param Request $request
     * @param Response $response
     * @return void
     */
    // public function onHandShake(Request $request, Response $response);

    /**
     * The function is executed when a new WebSocket connection is established and passed the handshake stage.
     * @see https://www.swoole.co.uk/docs/modules/swoole-websocket-server-on-open
     * @param Server $server
     * @param Request $request
     * @return void
     */
    public function onOpen(Server $server, Request $request);

    /**
     * The function is executed when a new data Frame is received by the WebSocket Server. The logics of processing the request from the WebSocket client should be defined within this function.
     * @see https://www.swoole.co.uk/docs/modules/swoole-websocket-server-on-message
     * @param Server $server
     * @param Frame $frame
     * @return void
     */
    public function onMessage(Server $server, Frame $frame);

    /**
     * The function is executed when a new WebSocket connection is closed.
     * @see https://www.swoole.co.uk/docs/modules/swoole-websocket-server-on-close
     * @param Server $server
     * @param $fd
     * @param $reactorId
     * @return void
     */
    public function onClose(Server $server, $fd, $reactorId);
}