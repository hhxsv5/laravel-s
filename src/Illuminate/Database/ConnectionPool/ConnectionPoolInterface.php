<?php

namespace Hhxsv5\LaravelS\Illuminate\Database\ConnectionPool;

use Illuminate\Database\ConnectionInterface;

interface ConnectionPoolInterface
{
    /**
     * @param string $name
     * @return \Illuminate\Database\ConnectionInterface
     */
    public function getConnection($name);

    /**
     * @param ConnectionInterface $connection
     * @return bool
     */
    public function returnConnection(ConnectionInterface $connection);
}