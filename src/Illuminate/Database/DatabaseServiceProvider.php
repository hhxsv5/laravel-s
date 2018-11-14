<?php

namespace Hhxsv5\LaravelS\Illuminate\Database;

use Hhxsv5\LaravelS\Illuminate\Database\ConnectionPool\ConnectionPools;
use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseServiceProvider as IlluminateDatabaseServiceProvider;

class DatabaseServiceProvider extends IlluminateDatabaseServiceProvider
{
    public function register()
    {
        parent::register();

        $this->app->singleton('db', function ($app) {
            $db = new DatabaseManager($app, $app['db.factory']);
            $version = $app->version();
            if (!version_compare($version, '5.2', '>=')) {
                throw new \Exception('Connection pool needs the version of Laravel/Lumen >= 5.2');
            }
            return $db;
        });

        $this->app->singleton('db.pool', function ($app) {
            $min = 2; // TODO
            $max = 4;
            $pools = new ConnectionPools($min, $max, function ($name) use ($app) {
                /**
                 * @var Connection $connection
                 */
                $connection = $app['db']->parentConnection($name);
                return $connection;
            });
            return $pools;
        });
    }
}