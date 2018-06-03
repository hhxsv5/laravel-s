<?php

namespace Hhxsv5\LaravelS\Swoole;

use Hhxsv5\LaravelS\Swoole\Socket\WebSocketInterface;

interface WebSocketHandlerInterface extends WebSocketInterface
{
    public function __construct();
}