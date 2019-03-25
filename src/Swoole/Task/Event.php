<?php

namespace Hhxsv5\LaravelS\Swoole\Task;

use Illuminate\Queue\SerializesModels;
use Swoole\Http\Server as HttpServer;
use Swoole\WebSocket\Server as WebSocketServer;

abstract class Event
{
    use SerializesModels;

    public static function fire(self $event)
    {
        /**@var HttpServer|WebSocketServer $swoole */
        $swoole = app('swoole');
        $taskId = $swoole->task($event);
        return $taskId !== false;
    }
}