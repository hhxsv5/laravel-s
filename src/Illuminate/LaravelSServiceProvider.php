<?php

namespace Hhxsv5\LaravelS\Illuminate;

use Illuminate\Support\ServiceProvider;

class LaravelSServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../Config/laravels.php' => config_path('laravels.php'),
        ], 'config');

    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/laravels.php', 'laravels'
        );

        $this->commands(LaravelSCommand::class);
    }

}