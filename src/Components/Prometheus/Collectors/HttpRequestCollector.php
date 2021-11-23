<?php

namespace Hhxsv5\LaravelS\Components\Prometheus\Collectors;

use Closure;
use Hhxsv5\LaravelS\Components\MetricCollector;
use Illuminate\Http\Request;

class HttpRequestCollector extends MetricCollector
{
    protected $routes          = [];
    protected $routesByUses    = [];
    protected $routesByClosure = [];

    public function __construct(array $config)
    {
        parent::__construct($config);

        $routes = method_exists(app(), 'getRoutes') ? app()->getRoutes() : app('router')->getRoutes();
        if ($routes instanceof \Illuminate\Routing\RouteCollection) { // Laravel
            foreach ($routes->getRoutes() as $route) {
                $method = $route->methods()[0];
                $uri = '/' . ltrim($route->uri(), '/');
                $this->routes[$method . $uri] = $uri;

                $action = $route->getAction();
                if (is_string($action['uses'])) { // Uses
                    $this->routesByUses[$method . $action['uses']] = $uri;
                } elseif ($action['uses'] instanceof Closure) {  // Closure
                    $objectId = spl_object_hash($action['uses']);
                    $this->routesByClosure[$method . $objectId] = $uri;
                }
            }
        } elseif (is_array($routes)) { // Lumen
            $this->routes = $routes;
            foreach ($routes as $route) {
                if (isset($route['action']['uses'])) { // Uses
                    $this->routesByUses[$route['method'] . $route['action']['uses']] = $route['uri'];
                }
                if (isset($route['action'][0]) && $route['action'][0] instanceof Closure) { // Closure
                    $objectId = spl_object_hash($route['action'][0]);
                    $this->routesByClosure[$route['method'] . $objectId] = $route['uri'];
                }
            }
        }
    }

    public function collect(array $params = [])
    {
        if (!$this->config['enable']) {
            return;
        }

        /**@var \Illuminate\Http\Request $request */
        /**@var \Illuminate\Http\Response $response */
        list($request, $response) = $params;

        $status = $response->getStatusCode();
        if (isset($this->config['ignored_http_codes'][$status])) {
            // Ignore the requests.
            return;
        }

        $uri = $this->findRouteUri($request);
        $cost = microtime(true) - $request->server('REQUEST_TIME_FLOAT');

        // Http Request Stats
        $requestLabels = http_build_query([
            'method' => $request->getMethod(),
            'uri'    => $uri,
            'status' => $status,
        ]);

        // Key Format: prefix+metric_name+metric_type+metric_labels
        $countKey = implode($this->config['apcu_key_separator'], [$this->config['apcu_key_prefix'], 'http_server_requests_seconds_count', 'summary', $requestLabels]);
        $sumKey = implode($this->config['apcu_key_separator'], [$this->config['apcu_key_prefix'], 'http_server_requests_seconds_sum', 'summary', $requestLabels]);
        apcu_inc($countKey, 1, $success, $this->config['apcu_key_max_age']);
        apcu_inc($sumKey, round($cost * 1000000), $success, $this->config['apcu_key_max_age']); // Time unit: μs
    }

    protected function findRouteUri(Request $request)
    {
        $method = $request->getMethod();
        $uri = $request->getPathInfo();
        $key = $method . $uri;
        if (isset($this->routes[$key])) {
            return $uri;
        }

        $route = $request->route();
        if ($route instanceof \Illuminate\Routing\Route) { // Laravel
            $uri = $route->uri();
        } elseif (is_array($route)) { // Lumen
            if (isset($route[1]['uses'])) {
                $key = $method . $route[1]['uses'];
                if (isset($this->routesByUses[$key])) {
                    $uri = $this->routesByUses[$key];
                }
            } elseif (isset($route[1][0]) && $route[1][0] instanceof Closure) {
                $key = $method . spl_object_hash($route[1][0]);
                if (isset($this->routesByClosure[$key])) {
                    $uri = $this->routesByClosure[$key];
                }
            }
        }
        return $uri;
    }
}