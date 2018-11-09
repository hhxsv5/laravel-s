<?php

namespace Hhxsv5\LaravelS\Swoole\Pool;

use Swoole\Coroutine\Channel;

class Pool implements PoolInterface
{
    /**
     * @var Channel
     */
    protected $pool;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $min;

    /**
     * @var int
     */
    protected $max;

    /**
     * @var callable
     */
    protected $resolver;

    public function __construct($name, $min, $max, callable $resolver)
    {
        if ($min < 1) {
            throw new \InvalidArgumentException('The min must be >= 1');
        }
        if ($max < $min) {
            throw new \InvalidArgumentException('The max must be >= min');
        }

        $this->name = $name;
        $this->min = $min;
        $this->max = $max;
        $this->resolver = $resolver;
    }

    public function init()
    {
        $this->pool = new Channel($this->max);
        for ($i = 0; $i < $this->min; $i++) {
            $connection = call_user_func($this->resolver, $this->name);
            $this->put($connection);
        }
    }

    public function getSize()
    {
        return $this->pool->length();
    }

    public function get()
    {
        return $this->pool->pop();
    }

    public function put($resource)
    {
        if ($this->pool->length() > $this->max) {
            return false;
        }
        return $this->pool->push($resource);
    }

    public function balance()
    {
        if ($this->min - $this->pool->length() > 0) {
            $resource = call_user_func($this->resolver, $this->name);
            $this->pool->push($resource);
        } else {
            if ($this->pool->length() - $this->max > 0) {
                $this->pool->pop();
            }
        }
    }

}