<?php

namespace Hhxsv5\LaravelS\Swoole\Task;

use Illuminate\Queue\SerializesModels;

abstract class Event
{
    use SerializesModels;

    public static function fire(self $event)
    {
        /**
         * @var \swoole_http_server $swoole
         */
        $swoole = app('swoole');
        $taskId = $swoole->task($event);
        return $taskId !== false;
    }
}