<?php

namespace Hhxsv5\LaravelS\Illuminate\Database\ConnectionPool;

interface ConnectionPoolInterface
{
    /**
     * ConnectionPoolInterface constructor
     * @param string $name The name of pool
     * @param int $min The minimum active count of connection
     * @param int $max The maximum count of connection
     * @return void
     */
    public function setConfig($name, $min, $max);

    /**
     * Get the size of connection pool
     * @return int
     */
    public function getSize();

    /**
     * Set a callback of connection resolver
     * @param callable $connectionResolver
     * @return mixed
     */
    public function setConnectionResolver(callable $connectionResolver);

    /**
     * Get a connection from pool
     * @return mixed
     */
    public function getConnection();

    /**
     * Put a connection into pool
     * @param mixed $connection
     * @return bool
     */
    public function putConnection($connection);

    /**
     * Make connection pool balanced
     * @return void
     */
    public function balance();
}