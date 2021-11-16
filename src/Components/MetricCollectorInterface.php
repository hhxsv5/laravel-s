<?php

namespace Hhxsv5\LaravelS\Components;

interface MetricCollectorInterface
{
    /**
     * Collect the metrics
     * @param array $params
     * @return mixed
     */
    public function collect(array $params = []);
}