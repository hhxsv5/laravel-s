<?php

namespace Hhxsv5\LaravelS\Components\Prometheus;

use Hhxsv5\LaravelS\Components\Prometheus\Collectors\HttpRequestCollector;
use Hhxsv5\LaravelS\Components\Prometheus\Collectors\SwooleProcessCollector;
use Hhxsv5\LaravelS\Components\Prometheus\Collectors\SwooleStatsCollector;
use Hhxsv5\LaravelS\Components\Prometheus\Collectors\SystemCollector;
use Illuminate\Support\ServiceProvider;

class PrometheusServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../../config/prometheus.php' => base_path('config/prometheus.php'),
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../../config/prometheus.php', 'prometheus'
        );
        $this->app->singleton(HttpRequestCollector::class, function ($app) {
            return new HttpRequestCollector($app['config']->get('prometheus'));
        });
        $this->app->singleton(SwooleProcessCollector::class, function ($app) {
            return new SwooleProcessCollector($app['config']->get('prometheus'));
        });
        $this->app->singleton(SwooleStatsCollector::class, function ($app) {
            return new SwooleStatsCollector($app['config']->get('prometheus'));
        });
        $this->app->singleton(SystemCollector::class, function ($app) {
            return new SystemCollector($app['config']->get('prometheus'));
        });
        $this->app->singleton(PrometheusExporter::class, function ($app) {
            return new PrometheusExporter($app['config']->get('prometheus'));
        });
    }

    public function provides()
    {
        return [HttpRequestCollector::class, SwooleProcessCollector::class, SwooleStatsCollector::class, SystemCollector::class, PrometheusExporter::class];
    }
}