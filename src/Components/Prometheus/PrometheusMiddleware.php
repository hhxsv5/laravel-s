<?php

namespace Hhxsv5\LaravelS\Components\Prometheus;

use Closure;

class PrometheusMiddleware
{
    private $prometheusExporter;

    public function __construct(PrometheusExporter $prometheusExporter)
    {
        $this->prometheusExporter = $prometheusExporter;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $this->prometheusExporter->observeRequest($request, $response);
        return $response;
    }
}
