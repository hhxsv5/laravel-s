<?php

namespace Hhxsv5\LaravelS\Components\Prometheus\Collectors;

use Hhxsv5\LaravelS\Components\MetricCollector;

class SwooleStatsCollector extends MetricCollector
{
    public function collect(array $params = [])
    {
        /**@var \Swoole\Http\Server $swoole */
        $swoole = app('swoole');
        $stats = $swoole->stats();
        // Get worker_num/task_worker_num from setting for the old Swoole.
        $setting = $swoole->setting;
        if (!isset($stats['worker_num'])) {
            $stats['worker_num'] = $setting['worker_num'];
        }
        if (!isset($stats['task_worker_num'])) {
            $stats['task_worker_num'] = isset($setting['task_worker_num']) ? $setting['task_worker_num'] : 0;
        }
        $metrics = [
            [
                'name'  => 'swoole_cpu_num',
                'type'  => 'gauge',
                'value' => swoole_cpu_num(),
            ],
            [
                'name'  => 'swoole_start_time',
                'type'  => 'gauge',
                'value' => $stats['start_time'],
            ],
            [
                'name'  => 'swoole_connection_num',
                'type'  => 'gauge',
                'value' => $stats['connection_num'],
            ],
            [
                'name'  => 'swoole_request_count',
                'type'  => 'gauge',
                'value' => $stats['request_count'],
            ],
            [
                'name'  => 'swoole_worker_num',
                'type'  => 'gauge',
                'value' => $stats['worker_num'],
            ],
            [
                'name'  => 'swoole_idle_worker_num',
                'type'  => 'gauge',
                'value' => isset($stats['idle_worker_num']) ? $stats['idle_worker_num'] : 0,
            ],
            [
                'name'  => 'swoole_task_worker_num',
                'type'  => 'gauge',
                'value' => $stats['task_worker_num'],
            ],
            [
                'name'  => 'swoole_task_idle_worker_num',
                'type'  => 'gauge',
                'value' => isset($stats['task_idle_worker_num']) ? $stats['task_idle_worker_num'] : 0,
            ],
            [
                'name'  => 'swoole_tasking_num',
                'type'  => 'gauge',
                'value' => isset($stats['tasking_num']) ? $stats['tasking_num'] : 0,
            ],
        ];
        $key = implode($this->config['apcu_key_separator'], [$this->config['apcu_key_prefix'], 'swoole_stats', '', '']);
        apcu_store($key, $metrics, $this->config['apcu_key_max_age']);
    }
}