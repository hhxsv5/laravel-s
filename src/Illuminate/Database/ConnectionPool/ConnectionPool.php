<?php

namespace Hhxsv5\LaravelS\Illuminate\Database\ConnectionPool;

use Swoole\Coroutine\Channel;

class ConnectionPool implements ConnectionPoolInterface
{
    /**
     * @var Channel[]
     */
    protected $pools;

    protected $minActive;

    protected $maxActive;

    public function __construct($minActive, $maxActive)
    {
        $this->minActive = $minActive;
        $this->maxActive = $maxActive;
    }

    protected function getPool($name)
    {
        if (!isset($this->pools[$name])) {
            $this->pools[$name] = new Channel($this->maxActive);
        }
        return $this->pools[$name];
    }

    public function getConnection($name)
    {
        return $this->getPool($name)->pop();
    }

    public function putConnection($name, $connection)
    {
        $pool = $this->getPool($name);
        if ($pool->length() > $this->maxActive) {
            return false;
        }
        return $pool->push($connection);
    }

}