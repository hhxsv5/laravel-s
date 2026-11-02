<?php

namespace Hhxsv5\LaravelS\Tests\Unit\Timer;

use Hhxsv5\LaravelS\Swoole\Timer\CronJob;
use Hhxsv5\LaravelS\Tests\TestCase;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\Attributes\DataProvider;

class CronJobTest extends TestCase
{
    use PHPMock;

    protected function tearDown(): void
    {
        CronJob::setGlobalTimerLockKey('');
        CronJob::setEnable(true);
        parent::tearDown();
    }

    #[DataProvider('lockCases')]
    public function testOnlyLockOwnerEnablesTimer(bool $exists, ?string $owner, $acquired, bool $expected): void
    {
        CronJob::setGlobalTimerLockKey('Orders');
        CronJob::setEnable(!$expected);
        $redis = $this->createMock(TimerRedis::class);
        $this->getFunctionMock('Hhxsv5\\LaravelS\\Swoole\\Timer', 'app')
            ->expects($this->atLeastOnce())->with('redis')->willReturn($redis);
        $this->getFunctionMock('Hhxsv5\\LaravelS\\Swoole\\Timer', 'swoole_get_local_ip')
            ->expects($this->once())->willReturn(['eth0' => '192.0.2.1']);
        $this->getFunctionMock('Hhxsv5\\LaravelS\\Swoole\\Timer', 'config')
            ->expects($this->once())->with('laravels.listen_port')->willReturn(5200);
        $redis->expects($this->once())->method('client')->willReturn($redis);
        $redis->expects($this->once())->method('exists')->with('laravels:timer:orders')->willReturn($exists);
        $redis->expects($exists ? $this->once() : $this->never())->method('get')
            ->with('laravels:timer:orders')->willReturn($owner);
        $redis->expects($exists ? $this->never() : $this->once())->method('set')
            ->with('laravels:timer:orders', '192.0.2.1:5200', 'ex', 60, 'nx')->willReturn($acquired);

        CronJob::checkSetEnable();

        $this->assertSame($expected, CronJob::isEnable());
    }

    public static function lockCases(): array
    {
        return [
            'current instance owns lock' => [true, '192.0.2.1:5200', null, true],
            'another instance owns lock' => [true, '192.0.2.2:5200', null, false],
            'lock disappears while checking' => [true, null, null, false],
            'acquired phpredis lock' => [false, null, true, true],
            'acquired predis lock' => [false, null, 'OK', true],
            'lost lock race' => [false, null, false, false],
        ];
    }

    public function testRenewalUsesNormalizedKeyAndRequestedExpiry(): void
    {
        CronJob::setGlobalTimerLockKey('MixedCase');
        $redis = $this->createMock(TimerRedis::class);
        $this->getFunctionMock('Hhxsv5\\LaravelS\\Swoole\\Timer', 'app')
            ->expects($this->once())->with('redis')->willReturn($redis);
        $redis->expects($this->once())->method('expire')->with('laravels:timer:mixedcase', 30)->willReturn(1);
        $this->assertTrue(CronJob::renewGlobalTimerLock(30));
    }
}

class TimerRedis
{
    public function client() {}
    public function exists($key) {}
    public function get($key) {}
    public function set($key, $value, $expiry, $seconds, $condition) {}
    public function expire($key, $seconds) {}
}
