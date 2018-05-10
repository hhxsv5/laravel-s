<?php

namespace Hhxsv5\LaravelS\Swoole\Socket;

interface TcpInterface
{
    public function onConnect(\swoole_server $server, $fd, $reactorId);

    public function onClose(\swoole_server $server, $fd, $reactorId);

    public function onReceive(\swoole_server $server, $fd, $reactorId, $data);

    public function onBufferFull(\swoole_server $server, $fd);

    public function onBufferEmpty(\swoole_server $server, $fd);
}