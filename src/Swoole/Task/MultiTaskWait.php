<?php

namespace Hhxsv5\LaravelS\Swoole\Task;

class MultiTaskWait
{
    /**
     * @var array|Task[]
     */
    protected $tasks;

    /**
     * @var float
     */
    protected $timeout;

    public function __construct($timeout = 0.5)
    {
        $this->tasks = [];
        $this->timeout = $timeout;
    }

    /**
     * @param Task $task
     * @return void
     */
    public function addTask(Task $task)
    {
        $this->addTasks([$task]);
    }

    /**
     * @param Task[] $tasks
     * @return void
     */
    public function addTasks(array $tasks)
    {
        foreach ($tasks as $task) {
            $task->setForMultiTask(true);
            $this->tasks[] = $task;
        }
    }

    /**
     * @return array|Task[]
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * @return float
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Deliver a multi-task
     * @return array|bool
     */
    public function deliver()
    {
        /**@var \Swoole\Http\Server $swoole */
        $swoole = app('swoole');
        // The worker_id of timer process is -1
        if ($swoole->worker_id === -1 || $swoole->taskworker) {
            $workerNum = isset($swoole->setting['worker_num']) ? $swoole->setting['worker_num'] : 0;
            $availableId = mt_rand(0, $workerNum - 1);
            return $swoole->sendMessage($this, $availableId);
        }
        return $swoole->taskWaitMulti($this->tasks, $this->timeout);
    }
}