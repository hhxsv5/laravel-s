<?php

namespace Hhxsv5\LaravelS\Swoole\Pool;

interface PoolInterface
{
    /**
     * Pool Constructor
     * @param string $name The name of pool
     * @param int $min The minimum active count of connection
     * @param int $max The maximum count of connection
     * @param callable $resolver
     */
    public function __construct($name, $min, $max, callable $resolver);

    /**
     * Get the current size of pool
     * @return int
     */
    public function getSize();

    /**
     * Get a resource from pool
     * @return mixed
     */
    public function get();

    /**
     * Put a resource into pool
     * @param mixed $resource
     * @return bool
     */
    public function put($resource);

    /**
     * Make resource pool balanced
     * @return void
     */
    public function balance();
}