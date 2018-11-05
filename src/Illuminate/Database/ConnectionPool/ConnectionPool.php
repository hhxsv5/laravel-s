<?php

namespace Hhxsv5\LaravelS\Illuminate\Database\ConnectionPool;

use Swoole\Coroutine\Channel;

class ConnectionPool implements ConnectionPoolInterface
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
    protected $connectionResolver;

    public function setConfig($name, $min, $max)
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
        swoole_event_add($this->pool, function () {
            \Log::info('make balance start', [$this->pool->length()]);
            $this->balance();
            \Log::info('make balance end', [$this->pool->length()]);
        });
    }

    public function getSize()
    {
        return $this->pool->length();
    }

    public function setConnectionResolver(callable $connectionResolver)
    {
        $this->connectionResolver = $connectionResolver;
    }

    public function getConnection()
    {
        return $this->pool->pop();
    }

    public function putConnection($connection)
    {
        if ($this->pool->length() > $this->max) {
            return false;
        }
        return $this->pool->push($connection);
    }

    public function balance()
    {
        if ($this->min - $this->pool->length() > 0) {
            $connection = call_user_func($this->connectionResolver, $this->name);
            $this->pool->push($connection);
        } else {
            if ($this->pool->length() - $this->max > 0) {
                $this->pool->pop();
            }
        }
    }

}