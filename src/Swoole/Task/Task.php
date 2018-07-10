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

    abstract public function handle();

    public static function deliver(self $task, $bySendMessage = false)
    {
        $deliver = function () use ($task, $bySendMessage) {
            /**
             * @var \swoole_http_server $swoole
             */
            $swoole = app('swoole');
            if ($bySendMessage) {
                $taskWorkerNum = isset($swoole->setting['task_worker_num']) ? $swoole->setting['task_worker_num'] : 0;
                if ($taskWorkerNum === 0) {
                    throw new \InvalidArgumentException('LaravelS: Asynchronous task needs to set task_worker_num > 0');
                }
                $workerNum = isset($swoole->setting['worker_num']) ? $swoole->setting['worker_num'] : 0;
                $totalNum = $workerNum + $taskWorkerNum;
                return $swoole->sendMessage($task, mt_rand($workerNum, $totalNum - 1));
            } else {
                $taskId = $swoole->task($task);
                return $taskId !== false;
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