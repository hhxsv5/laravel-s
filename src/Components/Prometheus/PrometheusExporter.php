<?php

namespace Hhxsv5\LaravelS\Components\Prometheus;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PrometheusExporter
{
    const REDNER_MIME_TYPE = 'text/plain; version=0.0.4';

    private $config;

    private static $secondsMetrics = [
        'http_server_requests_seconds_sum' => 'http_server_requests_seconds_sum',
    ];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function observeRequest(Request $request, Response $response)
    {
        $cost = microtime(true) - $request->server('REQUEST_TIME_FLOAT');
        $status = $response->getStatusCode();
        if (isset($this->config['ignored_http_codes'][$status])) {
            // Ignore the requests.
            return;
        }

        $labels = http_build_query([
            'method' => $request->getMethod(),
            'uri'    => $request->getPathInfo(),
            'status' => $status,
        ]);
        // prefix+metric_name+metric_type+metric_labels
        $countKey = implode($this->config['apcu_key_separator'], [$this->config['apcu_key_prefix'], 'http_server_requests_seconds_count', 'summary', $labels]);
        $sumKey = implode($this->config['apcu_key_separator'], [$this->config['apcu_key_prefix'], 'http_server_requests_seconds_sum', 'summary', $labels]);
        apcu_inc($countKey, 1, $success, $this->config['apcu_key_max_age']);
        // $cost to ms
        apcu_inc($sumKey, round($cost * 1000), $success, $this->config['apcu_key_max_age']);
    }

    public function getSystemLoadAvgMetrics()
    {
        $load = sys_getloadavg();
        return [
            [
                'name'  => 'system_load_average_1m',
                'help'  => '',
                'type'  => 'gauge',
                'value' => $load[0],
            ],
            [
                'name'  => 'system_load_average_5m',
                'help'  => '',
                'type'  => 'gauge',
                'value' => $load[1],
            ],
            [
                'name'  => 'system_load_average_15m',
                'help'  => '',
                'type'  => 'gauge',
                'value' => $load[2],
            ],
        ];
    }

    public function getSwooleMetrics()
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
        return [
            [
                'name'  => 'swoole_cpu_num',
                'help'  => '',
                'type'  => 'gauge',
                'value' => swoole_cpu_num(),
            ],
            [
                'name'  => 'swoole_start_time',
                'help'  => '',
                'type'  => 'gauge',
                'value' => $stats['start_time'],
            ],
            [
                'name'  => 'swoole_connection_num',
                'help'  => '',
                'type'  => 'gauge',
                'value' => $stats['connection_num'],
            ],
            [
                'name'  => 'swoole_request_count',
                'help'  => '',
                'type'  => 'gauge',
                'value' => $stats['request_count'],
            ],
            [
                'name'  => 'swoole_worker_num',
                'help'  => '',
                'type'  => 'gauge',
                'value' => $stats['worker_num'],
            ],
            [
                'name'  => 'swoole_idle_worker_num',
                'help'  => '',
                'type'  => 'gauge',
                'value' => isset($stats['idle_worker_num']) ? $stats['idle_worker_num'] : 0,
            ],
            [
                'name'  => 'swoole_task_worker_num',
                'help'  => '',
                'type'  => 'gauge',
                'value' => $stats['task_worker_num'],
            ],
            [
                'name'  => 'swoole_task_idle_worker_num',
                'help'  => '',
                'type'  => 'gauge',
                'value' => isset($stats['task_idle_worker_num']) ? $stats['task_idle_worker_num'] : 0,
            ],
            [
                'name'  => 'swoole_tasking_num',
                'help'  => '',
                'type'  => 'gauge',
                'value' => $stats['tasking_num'],
            ],
            [
                'name'  => 'swoole_coroutine_num',
                'help'  => '',
                'type'  => 'gauge',
                'value' => isset($stats['coroutine_num']) ? $stats['coroutine_num'] : 0,
            ],
        ];
    }

    public function getApcuMetrics()
    {
        $apcSmaInfo = apcu_sma_info(true);
        $metrics = [
            [
                'name'  => 'apcu_seg_size',
                'help'  => '',
                'type'  => 'gauge',
                'value' => $apcSmaInfo['seg_size'],
            ],
            [
                'name'  => 'apcu_avail_mem',
                'help'  => '',
                'type'  => 'gauge',
                'value' => $apcSmaInfo['avail_mem'],
            ],
        ];
        foreach (new \APCuIterator('/^' . $this->config['apcu_key_prefix'] . $this->config['apcu_key_separator'] . '/') as $item) {
            $value = apcu_fetch($item['key'], $success);
            if (!$success) {
                continue;
            }

            $parts = explode($this->config['apcu_key_separator'], $item['key']);
            parse_str($parts[3], $labels);
            $metrics[] = [
                'name'   => $parts[1],
                'help'   => '',
                'type'   => $parts[2],
                'value'  => isset(self::$secondsMetrics[$parts[1]]) ? $value / 1000 : $value,
                'labels' => $labels,
            ];
        }
        return $metrics;

    }

    public function render()
    {
        $defaultLabels = ['application' => $this->config['application']];
        $metrics = array_merge($this->getSystemLoadAvgMetrics(), $this->getSwooleMetrics(), $this->getApcuMetrics());
        $lines = [];
        foreach ($metrics as $metric) {
            $lines[] = "# HELP " . $metric['name'] . " {$metric['help']}";
            $lines[] = "# TYPE " . $metric['name'] . " {$metric['type']}";

            $metricLabels = isset($metric['labels']) ? $metric['labels'] : [];
            $labels = ['{'];
            $allLabels = array_merge($defaultLabels, $metricLabels);
            foreach ($allLabels as $key => $value) {
                $value = addslashes($value);
                $labels[] = "{$key}=\"{$value}\",";
            }
            $labels[] = '}';
            $labelStr = implode('', $labels);
            $lines[] = $metric['name'] . "$labelStr {$metric['value']}";
        }
        return implode("\n", $lines);
    }
}