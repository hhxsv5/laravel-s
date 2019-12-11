<?php

namespace Hhxsv5\LaravelS\Swoole\Task;

use Swoole\Timer;

trait TaskTrait
{
    /**
     * The number of seconds before the task should be delayed.
     *
     * @var int|null
     */
    protected $delay;

    public function delay($delay)
    {
        if ($delay !== null && $delay <= 0) {
            throw new \InvalidArgumentException('The delay must be greater than 0');
        }
        $this->delay = $delay;
        return $this;
    }

    /**
     * Delay in seconds, null means no delay.
     * @return int|null
     */
    public function getDelay()
    {
        return $this->delay;
    }

    /**
     * Deliver a task
     * @param mixed $task The task object
     * @return bool|mixed
     */
    public function task($task)
    {
        $deliver = function () use ($task) {
            /**@var \Swoole\Http\Server $swoole */
            $swoole = app('swoole');
            if ($swoole->taskworker) {
                $taskWorkerNum = isset($swoole->setting['task_worker_num']) ? (int)$swoole->setting['task_worker_num'] : 0;
                if ($taskWorkerNum < 2) {
                    throw new \InvalidArgumentException('LaravelS: async task needs to set task_worker_num >= 2');
                }
                $workerNum = isset($swoole->setting['worker_num']) ? $swoole->setting['worker_num'] : 0;
                $totalNum = $workerNum + $taskWorkerNum;

                $getAvailableId = function ($startId, $endId, $excludeId) {
                    $ids = range($startId, $endId);
                    $ids = array_flip($ids);
                    unset($ids[$excludeId]);
                    return array_rand($ids);
                };
                $availableId = $getAvailableId($workerNum, $totalNum - 1, $swoole->worker_id);
                return $swoole->sendMessage($task, $availableId);
            } else {
                $taskId = $swoole->task($task);
                return $taskId !== false;
            }
        };
        if ($this->delay !== null && $this->delay > 0) {
            Timer::after($this->delay * 1000, $deliver);
            return true;
        } else {
            return $deliver();
        }
    }
}