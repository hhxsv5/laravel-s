<?php

namespace Hhxsv5\LaravelS\Components\Prometheus;

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

        $this->app->singleton(PrometheusExporter::class, function ($app) {
            return new PrometheusExporter($app['config']->get('prometheus'));
        });
    }

    public function provides()
    {
        return [PrometheusExporter::class];
    }
}