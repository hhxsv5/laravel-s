<?php

namespace Hhxsv5\LaravelS\Illuminate\Database;

use Hhxsv5\LaravelS\Illuminate\Database\Connectors\CoroutineMySQLConnector;
use Illuminate\Database\Connectors\ConnectionFactory as IlluminateConnectionFactory;

class ConnectionFactory extends IlluminateConnectionFactory
{
    public function createConnector(array $config)
    {
        if (!isset($config['driver'])) {
            throw new \InvalidArgumentException('A driver must be specified.');
        }

        switch ($config['driver']) {
            case 'sw-co-mysql':
                return new CoroutineMySQLConnector();
        }
        return parent::createConnector($config);
    }

    protected function createConnection($driver, $connection, $database, $prefix = '', array $config = [])
    {
        switch ($driver) {
            case 'sw-co-mysql':
                return new SwooleMySQLConnection($connection, $database, $prefix, $config);
        }
        return parent::createConnection($driver, $connection, $database, $prefix, $config);
    }
}