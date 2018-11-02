<?php

namespace Hhxsv5\LaravelS\Illuminate\Database;

use Hhxsv5\LaravelS\Illuminate\Database\ConnectionPool\LaravelConnectionPool;
use Illuminate\Database\DatabaseServiceProvider as IlluminateDatabaseServiceProvider;

class DatabaseServiceProvider extends IlluminateDatabaseServiceProvider
{
    public function register()
    {
        parent::register();

        $this->app->singleton('db', function ($app) {
            return new DatabaseManager($app, $app['db.factory']);
        });

        $this->app->singleton('db.pool', function ($app) {
            $minActive = 2;
            $maxActive = 4;
            $pool = new LaravelConnectionPool($minActive, $maxActive, $app);
            $pool->setConnectionResolver(function ($name) use ($app) {
                return $app['db']->parentConnection($name);
            });
            return $pool;
        });
    }
}