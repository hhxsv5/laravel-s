<?php

namespace Hhxsv5\LaravelS\Components\Prometheus;

use Closure;

class PrometheusMiddleware
{
    private $prometheusExporter;
    private $isObserveRequest;

    public function __construct(PrometheusExporter $prometheusExporter)
    {
        $this->prometheusExporter = $prometheusExporter;
        $this->isObserveRequest = (bool)config('prometheus.observe_request');
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
        if (!$this->isObserveRequest) {
            return $response;
        }
        try {
            $this->prometheusExporter->observeRequest($request, $response);
        } catch (\Exception $e) {
            app('log')->error('PrometheusMiddleware: failed to observe request', ['exception' => $e]);
        } finally {
            return $response;
        }
    }
}
