<?php

namespace Hhxsv5\LaravelS\Swoole\Task;

use Illuminate\Queue\SerializesModels;

abstract class Task
{
    use SerializesModels;

    /**
     * The number of seconds before the task should be delayed.
     *
     * @var int|null
     */
    protected $delay;

    public function delay($delay)
    {
        if ($delay <= 0) {
            throw new \InvalidArgumentException('The delay must be greater than 0');
        }
        if ($delay >= 86400) {
            throw new \InvalidArgumentException('The max delay is 86400s');
        }
        $this->delay = $delay;
        return $this;
    }

    public function getDelay()
    {
        return $this->delay;
    }

    public function onException(\Exception $e)
    {

    }

    abstract public function handle();

    public static function deliver(self $task)
    {
        $deliver = function () use ($task) {
            try {
                $taskId = app('swoole')->task($task);
                return $taskId !== false;
            } catch (\Exception $e) {
                return false;
            }
        };
        if ($task->delay > 0) {
            swoole_timer_after($task->delay * 1000, $deliver);
            return true;
        } else {
            return $deliver();
        }
    }
}