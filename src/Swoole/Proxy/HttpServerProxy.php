<?php

namespace Hhxsv5\LaravelS\Swoole\Proxy;

use Swoole\Http\Server;

/**
 * HTTP Server Proxy for PHP 8.2+ dynamic properties compatibility
 *
 * Extends Swoole\Http\Server to support dynamic properties (e.g., Swoole Tables)
 * using the #[\AllowDynamicProperties] attribute.
 */
#[\AllowDynamicProperties]
class HttpServerProxy extends Server
{
}
