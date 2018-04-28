<?php

namespace Hhxsv5\LaravelS\Illuminate\Database;

use Illuminate\Database\DatabaseServiceProvider as IlluminateDatabaseServiceProvider;

class DatabaseServiceProvider extends IlluminateDatabaseServiceProvider
{
    public function register()
    {
//        $this->app->singleton('db.factory', function ($app) {
//            return new ConnectionFactory($app);
//        });
//
//        $this->app->singleton('db', function ($app) {
//            return new DatabaseManager($app, $app['db.factory']);
//        });
//
//        $this->app->bind('db.connection', function ($app) {
//            return $app['db']->connection();
//        });
    }
}