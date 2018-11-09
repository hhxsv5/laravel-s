<?php

namespace Hhxsv5\LaravelS\Illuminate\Database;

use Hhxsv5\LaravelS\Illuminate\Database\ConnectionPool\ConnectionPools;
use Illuminate\Database\DatabaseServiceProvider as IlluminateDatabaseServiceProvider;

class DatabaseServiceProvider extends IlluminateDatabaseServiceProvider
{
    public function register()
    {
        parent::register();

        $this->app->singleton('db', function ($app) {
            $db = new DatabaseManager($app, $app['db.factory']);
            $version = $app->version();
            if (version_compare($version, '5.2', '>=')) {
                throw new \Exception('Connection pool needs >= 5.2');
                return $db;
            }
            $db->listen(function ($query) {

            });
        });

        $this->app->singleton('db.pool', function ($app) {
            $min = 2; // TODO
            $max = 4;
            $pools = new ConnectionPools($min, $max, function ($name) use ($app) {
                return $app['db']->parentConnection($name);
            });
            return $pools;
        });
    }
}