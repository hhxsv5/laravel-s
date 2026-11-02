<?php

namespace Hhxsv5\LaravelS\Tests\Unit\Task;

use Hhxsv5\LaravelS\Swoole\Task\BaseTask;
use Hhxsv5\LaravelS\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class BaseTaskTest extends TestCase
{
    public function testDefaultsAndFluentConfiguration(): void
    {
        $task = new class extends BaseTask {};

        $this->assertSame(0, $task->getDelay());
        $this->assertSame(1, $task->getTries());
        $this->assertSame($task, $task->delay(3)->setTries(5));
        $this->assertSame(3, $task->getDelay());
        $this->assertSame(5, $task->getTries());
        $task->delay(0)->setTries(1);
        $this->assertSame(0, $task->getDelay());
        $this->assertSame(1, $task->getTries());
    }

    #[DataProvider('invalidArguments')]
    public function testRejectsInvalidConfiguration(string $method, int $value, string $message): void
    {
        $task = new class extends BaseTask {};

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);
        $task->$method($value);
    }

    public static function invalidArguments(): array
    {
        return [
            'negative delay' => ['delay', -1, 'The delay must be greater than or equal to 0'],
            'zero tries' => ['setTries', 0, 'The number of attempts must be greater than or equal to 1'],
            'negative tries' => ['setTries', -3, 'The number of attempts must be greater than or equal to 1'],
        ];
    }
}
