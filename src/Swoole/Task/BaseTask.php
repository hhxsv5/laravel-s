<?php

namespace Hhxsv5\LaravelS\Swoole\Task;

use Swoole\Timer;

abstract class BaseTask
{
    /**
     * The number of seconds before the task should be delayed.
     * @var int
     */
    protected $delay = 0;

    /**
     * The number of tries.
     * @var int
     */
    protected $tries = 1;

    /**
     * Delay in seconds, null means no delay.
     * @param int $delay
     * @return $this
     */
    public function delay($delay)
    {
        if ($delay < 0) {
            throw new \InvalidArgumentException('The delay must be greater than or equal to 0');
        }
        $this->delay = (int)$delay;
        return $this;
    }

    /**
     * Return the delay time.
     * @return int
     */
    public function getDelay()
    {
        return $this->delay;
    }

    /**
     * Set the number of tries.
     * @param int $tries
     * @return $this
     */
    public function setTries($tries)
    {
        if ($tries < 1) {
            throw new \InvalidArgumentException('The number of attempts must be greater than or equal to 1');
        }
        $this->tries = (int)$tries;
        return $this;
    }

    /**
     * Get the number of tries.
     * @return int
     */
    public function getTries()
    {
        return $this->tries;
    }

    /**
     * Deliver a task
     * @param mixed $task The task object
     * @return bool
     */
    protected function task($task)
    {
        static $dispatch;
        if (!$dispatch) {
            $dispatch = static function ($task) {
                /**@var \Swoole\Http\Server $swoole */
                $swoole = app('swoole');
                // The worker_id of timer process is -1
                if ($swoole->worker_id === -1 || $swoole->taskworker) {
                    $workerNum = isset($swoole->setting['worker_num']) ? $swoole->setting['worker_num'] : 0;
                    $availableId = mt_rand(0, $workerNum - 1);
                    return $swoole->sendMessage($task, $availableId);
                }
                $taskId = $swoole->task($task);
                return $taskId !== false;
            };
        }

        if ($this->delay > 0) {
            Timer::after($this->delay * 1000, $dispatch, $task);
            return true;
        }

        return $dispatch($task);
    }
}