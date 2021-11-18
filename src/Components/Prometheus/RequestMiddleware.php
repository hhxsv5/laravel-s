<?php

namespace Hhxsv5\LaravelS\Components\Prometheus;

use Closure;
use Hhxsv5\LaravelS\Components\Prometheus\Collectors\HttpRequestCollector;

class RequestMiddleware
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
        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Http\Response $response
     * @return void
     */
    public function terminate($request, $response)
    {
        try {
            $this->collector->collect([$request, $response]);
        } catch (\Exception $e) {
            app('log')->error('PrometheusMiddleware: failed to collect request metrics.', ['exception' => $e]);
        }
    }
}
