<?php

namespace Hhxsv5\LaravelS\Swoole\Task;

use Illuminate\Queue\SerializesModels;

abstract class Event
{
    use SerializesModels;

    public static function fire(self $event)
    {
        try {
            $taskId = app('swoole')->task($event);
            return $taskId !== false;
        } catch (\Exception $e) {
            return false;
        }
    }
}