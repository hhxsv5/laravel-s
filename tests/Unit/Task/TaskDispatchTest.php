<?php

namespace Hhxsv5\LaravelS\Tests\Unit\Task;

use Hhxsv5\LaravelS\Swoole\Task\BaseTask;
use Hhxsv5\LaravelS\Tests\TestCase;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\Attributes\DataProvider;

class TaskDispatchTest extends TestCase
{
    use PHPMock;

    #[DataProvider('taskResults')]
    public function testWorkerDispatchTreatsTaskIdZeroAsSuccess($taskId, bool $expected): void
    {
        $server = $this->createMock(DispatchServer::class);
        $task = $this->task();
        $this->getFunctionMock('Hhxsv5\\LaravelS\\Swoole\\Task', 'app')
            ->expects($this->once())->with('swoole')->willReturn($server);
        $server->expects($this->once())->method('task')->with($task)->willReturn($taskId);
        $server->expects($this->never())->method('sendMessage');

        $this->assertSame($expected, $task->dispatch());
    }

    public static function taskResults(): array
    {
        return ['first task' => [0, true], 'later task' => [42, true], 'failed task' => [false, false]];
    }

    #[DataProvider('processTypes')]
    public function testNonWorkerProcessesForwardToAWorker(int $workerId, bool $taskWorker, bool $result): void
    {
        $server = $this->createMock(DispatchServer::class);
        $server->worker_id = $workerId;
        $server->taskworker = $taskWorker;
        $task = $this->task();
        $this->getFunctionMock('Hhxsv5\\LaravelS\\Swoole\\Task', 'app')
            ->expects($this->once())->with('swoole')->willReturn($server);
        $server->expects($this->never())->method('task');
        $server->expects($this->once())->method('sendMessage')->with($task, 0)->willReturn($result);

        $this->assertSame($result, $task->dispatch());
    }

    public static function processTypes(): array
    {
        return [
            'timer process' => [-1, false, true],
            'task worker' => [1, true, true],
            'failed forwarding' => [-1, false, false],
        ];
    }

    private function task(): BaseTask
    {
        return new class extends BaseTask {
            public function dispatch(): bool
            {
                return $this->task($this);
            }
        };
    }
}

class DispatchServer
{
    public $worker_id = 0;
    public $taskworker = false;
    public $setting = ['worker_num' => 1];

    public function task($task) {}

    public function sendMessage($task, $workerId) {}
}
