<?php

namespace Hhxsv5\LaravelS\Components\Prometheus;

interface PrometheusCollectorInterface
{
    /**
     * Collect the metrics
     * @param array $params
     * @return mixed
     */
    public function collect(array $params = []);
}