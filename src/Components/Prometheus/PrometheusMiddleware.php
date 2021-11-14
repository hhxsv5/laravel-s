<?php

namespace Hhxsv5\LaravelS\Components\Prometheus;

use Closure;
use Hhxsv5\LaravelS\Components\Prometheus\Collectors\HttpRequestCollector;

class PrometheusMiddleware
{
    private $collector;

    public function __construct(HttpRequestCollector $collector)
    {
        $this->collector = $collector;
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
            $this->collector->collect([$request, $response]);
        } catch (\Exception $e) {
            app('log')->error('PrometheusMiddleware: failed to collect request metrics.', ['exception' => $e]);
        } finally {
            return $response;
        }
    }
}
