<?php

namespace Hhxsv5\LaravelS\Illuminate\Database;

use Illuminate\Database\DatabaseManager as IlluminateDatabaseManager;

class DatabaseManager extends IlluminateDatabaseManager
{
    public function connection($name = null)
    {
        $connection = $this->app['db.pool']->getConnection($name);
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
        $this->app['db.pool']->putConnection($name, parent::connection($name));
    }
}