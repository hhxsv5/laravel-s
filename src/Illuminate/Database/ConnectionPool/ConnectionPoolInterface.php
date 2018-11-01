<?php

namespace Hhxsv5\LaravelS\Illuminate\Database\ConnectionPool;

interface ConnectionPoolInterface
{
    /**
     * Get a connection from pool
     * @param string $name
     * @return mixed
     */
    public function getConnection($name);

    /**
     * Put a connection into pool
     * @param string $name
     * @param mixed $connection
     * @return bool
     */
    public function putConnection($name, $connection);
}