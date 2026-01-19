<?php

namespace Hhxsv5\LaravelS\Swoole\Proxy;

use Swoole\WebSocket\Server;

/**
 * WebSocket Server Proxy for PHP 8.2+ dynamic properties compatibility
 *
 * Extends Swoole\WebSocket\Server to support dynamic properties (e.g., Swoole Tables)
 * using the #[\AllowDynamicProperties] attribute.
 */
#[\AllowDynamicProperties]
class WebSocketServerProxy extends Server
{
}
