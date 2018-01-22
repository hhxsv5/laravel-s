<?php

namespace Hhxsv5\LaravelS\Illuminate;

use Illuminate\Support\ServiceProvider;

class LaravelSServiceProvider extends ServiceProvider
{

    protected $defer = true;

    public function boot()
    {
        //
    }

    public function register()
    {
        $this->commands(LaravelSCommand::class);
    }

}