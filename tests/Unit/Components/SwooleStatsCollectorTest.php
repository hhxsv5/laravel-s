<?php

namespace Hhxsv5\LaravelS\Tests\Unit\Components;

use Hhxsv5\LaravelS\Components\Prometheus\Collectors\SwooleStatsCollector;
use Hhxsv5\LaravelS\Tests\TestCase;
use phpmock\phpunit\PHPMock;

class SwooleStatsCollectorTest extends TestCase
{
    use PHPMock;

    public function testUsesLegacyServerSettingsAndDefaults(): void
    {
        $values = $this->collectStats([], ['worker_num' => 4]);

        $this->assertSame(4, $values['swoole_worker_num']);
        $this->assertSame(0, $values['swoole_task_worker_num']);
        $this->assertSame(0, $values['swoole_idle_worker_num']);
        $this->assertSame(0, $values['swoole_task_idle_worker_num']);
        $this->assertSame(0, $values['swoole_tasking_num']);
    }

    public function testPrefersRuntimeStatsOverSettings(): void
    {
        $values = $this->collectStats([
            'worker_num' => 8, 'task_worker_num' => 3,
            'idle_worker_num' => 2, 'task_idle_worker_num' => 1, 'tasking_num' => 5,
        ], ['worker_num' => 4, 'task_worker_num' => 2]);

        $this->assertSame(8, $values['swoole_worker_num']);
        $this->assertSame(3, $values['swoole_task_worker_num']);
        $this->assertSame(2, $values['swoole_idle_worker_num']);
        $this->assertSame(1, $values['swoole_task_idle_worker_num']);
        $this->assertSame(5, $values['swoole_tasking_num']);
    }

    private function collectStats(array $stats, array $settings): array
    {
        $server = $this->createMock(StatsServer::class);
        $server->setting = $settings;
        $server->method('stats')->willReturn($stats + [
            'start_time' => 123, 'connection_num' => 6, 'request_count' => 10,
        ]);
        $namespace = 'Hhxsv5\\LaravelS\\Components\\Prometheus\\Collectors';
        $this->getFunctionMock($namespace, 'app')->expects($this->once())->with('swoole')->willReturn($server);
        $this->getFunctionMock($namespace, 'swoole_cpu_num')->expects($this->once())->willReturn(4);
        $metrics = null;
        $this->getFunctionMock($namespace, 'apcu_store')->expects($this->once())
            ->willReturnCallback(function ($key, $data, $ttl) use (&$metrics) {
                $this->assertSame('laravels:swoole_stats::', $key);
                $this->assertSame(60, $ttl);
                $metrics = $data;
                return true;
            });

        (new SwooleStatsCollector([
            'apcu_key_prefix' => 'laravels', 'apcu_key_separator' => ':', 'apcu_key_max_age' => 60,
        ]))->collect();

        $values = [];
        foreach ($metrics as $metric) {
            $this->assertSame('gauge', $metric['type']);
            $values[$metric['name']] = $metric['value'];
        }
        $this->assertSame(4, $values['swoole_cpu_num']);
        $this->assertSame(123, $values['swoole_start_time']);
        $this->assertSame(6, $values['swoole_connection_num']);
        $this->assertSame(10, $values['swoole_request_count']);
        return $values;
    }
}

class StatsServer
{
    public $setting;

    public function stats() {}
}
