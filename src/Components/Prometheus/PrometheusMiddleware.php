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
        try {
            $this->prometheusExporter->observeRequest($request, $response);
        } catch (\Exception $e) {
            app('log')->error('PrometheusMiddleware: failed to observe request', ['exception' => $e]);
        } finally {
            return $response;
        }
    }
}
