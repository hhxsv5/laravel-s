<?php

namespace Hhxsv5\LaravelS\Illuminate;

use Hhxsv5\LaravelS\Console\ListPropertiesCommand;
use Illuminate\Support\ServiceProvider;

class LaravelSServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/laravels.php' => base_path('config/laravels.php'),
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/laravels.php', 'laravels'
        );

        $this->commands([
            LaravelSCommand::class,
            ListPropertiesCommand::class,
        ]);
    }
}