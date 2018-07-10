<?php

namespace Hhxsv5\LaravelS\Illuminate\Database;

use Illuminate\Database\DatabaseManager as IlluminateDatabaseManager;

class DatabaseManager extends IlluminateDatabaseManager
{
    public function __construct($app, ConnectionFactory $factory)
    {
        parent::__construct($app, $factory);
    }

//    public function connection($name = null)
//    {
//        $this->connections = [];
//        return parent::connection($name);
//    }
}