<?php

namespace Hhxsv5\LaravelS\Components\Prometheus\Collectors;

use Hhxsv5\LaravelS\Components\Prometheus\PrometheusCollector;

class HttpRequestCollector extends PrometheusCollector
{
    public function collect(array $params = [])
    {
        /**@var \Illuminate\Http\Request $request */
        /**@var \Illuminate\Http\Response $response */
        list($request, $response) = $params;
        if (!$this->config['observe_request']) {
            return;
        }

        $cost = microtime(true) - $request->server('REQUEST_TIME_FLOAT');
        $status = $response->getStatusCode();
        if (isset($this->config['ignored_http_codes'][$status])) {
            // Ignore the requests.
            return;
        }

        // Http Request Stats
        $requestLabels = http_build_query([
            'method' => $request->getMethod(),
            'uri'    => $request->getPathInfo(),
            'status' => $status,
        ]);
        // Key Format: prefix+metric_name+metric_type+metric_labels
        $countKey = implode($this->config['apcu_key_separator'], [$this->config['apcu_key_prefix'], 'http_server_requests_seconds_count', 'summary', $requestLabels]);
        $sumKey = implode($this->config['apcu_key_separator'], [$this->config['apcu_key_prefix'], 'http_server_requests_seconds_sum', 'summary', $requestLabels]);
        apcu_inc($countKey, 1, $success, $this->config['apcu_key_max_age']);
        apcu_inc($sumKey, round($cost * 1000000), $success, $this->config['apcu_key_max_age']); // Time unit: μs
    }
}