<?php

namespace Hhxsv5\LaravelS\Swoole\Socket;

use Swoole\Http\Request;
use Swoole\Http\Response;

interface HttpInterface
{
    public function onRequest(Request $request, Response $response);
}