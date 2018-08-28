<?php

namespace Hhxsv5\LaravelS\Illuminate\Database\ConnectionPool;

use Illuminate\Database\ConnectionInterface;
use Swoole\Coroutine\Channel;

abstract class AbstractConnectionPool implements ConnectionPoolInterface
{
    /**
     * @var Channel
     */
    protected $channel;

    protected $minActive;

    protected $maxActive;

    public function __construct($minActive, $maxActive)
    {
        $this->minActive = $minActive;
        $this->maxActive = $maxActive;
        $this->channel = new Channel($maxActive);
    }

    protected function createConnection($name)
    {
        return app('db')->connection($name);
    }

    public function getConnection($name)
    {
        $stats = $this->channel->stats();
        if ($stats['queue_num'] > $this->minActive) {
            return $this->channel->pop();
        } else {
            return $this->createConnection($name);
        }
    }

    public function returnConnection(ConnectionInterface $connection)
    {
        // TODO: Implement returnConnection() method.
    }

}