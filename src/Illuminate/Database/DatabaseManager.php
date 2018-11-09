<?php

namespace Hhxsv5\LaravelS\Illuminate\Database;

use Illuminate\Database\DatabaseManager as IlluminateDatabaseManager;

class DatabaseManager extends IlluminateDatabaseManager
{
    public function connection($name = null)
    {
        $pool = $this->app['db.pool']->getPool($name);
        $connection = $pool->get();
        \Log::info(__METHOD__, [$name, get_class($connection)]);
        return $connection;
    }

    public function parentConnection($name = null)
    {
        return parent::connection($name);
    }

    public function disconnect($name = null)
    {
        \Log::info(__METHOD__, [$name]);
        $pool = $this->app['db.pool']->getPool($name);
        $pool->put($name, parent::connection($name));
    }
}