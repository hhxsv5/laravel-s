<?php

namespace Hhxsv5\LaravelS\Illuminate\Database\ConnectionPool;

class LaravelConnectionPools
{

    /**
     * @var int
     */
    protected $min;

    /**
     * @var int
     */
    protected $max;

    /**
     * @var ConnectionPoolInterface[]
     */
    protected $pools = [];

    /**
     * @var callable
     */
    protected $connectionsResolver;

    public function __construct($min, $max)
    {
        $this->min = $min;
        $this->max = $max;
    }

    public function getPool($name)
    {
        if (isset($this->pools[$name])) {
            return $this->pools[$name];
        }
        $pool = new ConnectionPool();
        $pool->setConfig($name, $this->min, $this->max);
        $pool->setConnectionResolver(function () use ($name) {
            return $this->connectionsResolver($name);
        });
        return $this->pools[$name] = $pool;
    }

    public function setConnectionsResolver(callable $connectionsResolver)
    {
        $this->connectionsResolver = $connectionsResolver;
    }

}