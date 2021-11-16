<?php

namespace Hhxsv5\LaravelS\Components\Prometheus\Collectors;

use Hhxsv5\LaravelS\Components\MetricCollector;

class SystemCollector extends MetricCollector
{
    public function collect(array $params = [])
    {
        $load = sys_getloadavg();
        $metrics = [
            [
                'name'  => 'system_load_average_1m',
                'type'  => 'gauge',
                'value' => $load[0],
            ],
            [
                'name'  => 'system_load_average_5m',
                'type'  => 'gauge',
                'value' => $load[1],
            ],
            [
                'name'  => 'system_load_average_15m',
                'type'  => 'gauge',
                'value' => $load[2],
            ],
        ];
        $key = implode($this->config['apcu_key_separator'], [$this->config['apcu_key_prefix'], 'system_stats', '', '']);
        apcu_store($key, $metrics, $this->config['apcu_key_max_age']);
    }
}