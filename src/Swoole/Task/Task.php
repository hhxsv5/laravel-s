<?php

namespace Hhxsv5\LaravelS\Swoole\Task;

use Illuminate\Queue\SerializesModels;

abstract class Task
{
    use SerializesModels;

    abstract public function handle();

    public static function deliver(self $task)
    {
        try {
            $taskId = app('swoole')->task($task);
            return $taskId !== false;
        } catch (\Exception $e) {
            return false;
        }
    }
}