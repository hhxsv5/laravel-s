<?php

namespace Hhxsv5\LaravelS\Illuminate\Database\ConnectionPool;

interface ConnectionPoolInterface
{
    /**
     * Set a callback of connection resolver
     * @param callable $connectionResolver
     * @return mixed
     */
    public function setConnectionResolver(callable $connectionResolver);

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

    /**
     * Get the size of connection pool
     * @param string $name
     * @return int
     */
    public function getPoolSize($name);
}