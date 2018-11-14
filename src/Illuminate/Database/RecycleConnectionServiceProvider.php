<?php

namespace Hhxsv5\LaravelS\Illuminate\Database;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class RecycleConnectionServiceProvider extends ServiceProvider
{
    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->bound('swoole')) {
            DB::listen(function (QueryExecuted $query) {
                Log::info(__METHOD__, [$query->sql]);
            });
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }
}
