<?php

namespace Hhxsv5\LaravelS\Swoole\Task;

use Illuminate\Queue\SerializesModels;

abstract class Task extends BaseTask
{
    use SerializesModels;

    /**
     * The logic of handling task
     * @return mixed
     */
    abstract public function handle();

    /**
     * Deliver a task
     * @param self $task The task object
     * @return bool
     */
    public static function deliver(BaseTask $task)
    {
        return parent::deliver($task);
    }
}