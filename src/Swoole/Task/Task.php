<?php

namespace Hhxsv5\LaravelS\Swoole\Task;

class Task
{
    public static function delivery(Event $event)
    {
        $taskId = app('swoole')->task($event);
        return $taskId !== false;
    }
}