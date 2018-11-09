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
        $this->pool = new Channel($this->max);
        $this->resolver = $resolver;
        $this->fill();
    }

    protected function fill()
    {
        go(function () {
            $count = $this->min - $this->size();
            for ($i = 0; $i < $count; $i++) {
                $resource = call_user_func($this->resolver, $this->name);
                $this->put($resource);
            }
        });
    }

    public function size()
    {
        return $this->pool->length();
    }

    public function get()
    {
        if ($this->size() == 0) {
            $this->fill();
        }
        return $this->pool->pop();
    }

    public function put($resource)
    {
        if ($this->pool->length() > $this->max) {
            return false;
        }
        return $this->pool->push($resource);
    }

}