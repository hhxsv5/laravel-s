<?php

namespace Hhxsv5\LaravelS\Illuminate\Database\ConnectionPool;

use Hhxsv5\LaravelS\Swoole\Pool\Pool;
use Hhxsv5\LaravelS\Swoole\Pool\PoolInterface;

class ConnectionPools
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
     * @var PoolInterface[]
     */
    protected $pools = [];

    /**
     * @var callable
     */
    protected $resolver;

    public function __construct($min, $max, callable $resolver)
    {
        $this->min = $min;
        $this->max = $max;
        $this->resolver = $resolver;
    }

    public function getPool($name)
    {
        if (isset($this->pools[$name])) {
            return $this->pools[$name];
        }
        $pool = new Pool($name, $this->min, $this->max, function () use ($name) {
            return call_user_func($this->resolver, $name);
        });
        $pool->init();
        return $this->pools[$name] = $pool;
    }

}