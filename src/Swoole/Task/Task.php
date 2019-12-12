<?php

namespace Hhxsv5\LaravelS\Swoole\Task;

use Illuminate\Queue\SerializesModels;

abstract class Task extends BaseTask
{
    use SerializesModels;

    /**
     * The logic of handling task
     * @return void
     */
    abstract public function handle();

    /**
     * Deliver a task
     * @param Task $task The task object
     * @return bool
     */
    public static function deliver(self $task)
    {
        return $task->task($task);
    }
}