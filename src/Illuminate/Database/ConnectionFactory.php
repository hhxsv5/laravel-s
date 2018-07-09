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

        if ($this->container->bound($key = "db.connector.{$config['driver']}")) {
            return $this->container->make($key);
        }

        switch ($config['driver']) {
            case 'sw-co-mysql':
                return new CoroutineMySQLConnector();
        }
        return parent::createConnector($config);
    }

    protected function createSingleConnection(array $config)
    {
        $swoolePdo = $this->createConnector($config)->connect($config);
        return $this->createSwooleConnection($config['driver'], $swoolePdo, $config['database'], $config['prefix'], $config);
    }

    protected function createSwooleConnection($driver, SwoolePDO $connection, $database, $prefix = '', array $config = [])
    {
        if ($this->container->bound($key = "db.connection.{$driver}")) {
            return $this->container->make($key, [$connection, $database, $prefix, $config]);
        }
        switch ($driver) {
            case 'sw-co-mysql':
                return new SwooleMySQLConnection($connection, $database, $prefix, $config);
        }
        return parent::createConnection($driver, $connection, $database, $prefix, $config);
    }
}