<?php

namespace Hhxsv5\LaravelS\Illuminate\Database;

use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use Illuminate\Database\MySqlConnection;

class SwooleMySQLConnection extends MySqlConnection
{
    /**
     * The active swoole mysql connection.
     *
     * @var SwoolePDO
     */
    protected $pdo;

    /**
     * The active swoole mysql used for reads.
     *
     * @var SwoolePDO
     */
    protected $readPdo;

    public function getDriverName()
    {
        return 'Swoole Coroutine MySQL';
    }

    protected function tryAgainIfCausedByLostConnection(QueryException $e, $query, $bindings, \Closure $callback)
    {
        if ($this->causedByLostConnection($e->getPrevious()) || Str::contains($e->getMessage(), ['is closed', 'is not established'])) {
            $this->reconnect();

            return $this->runQueryCallback($query, $bindings, $callback);
        }

        throw $e;
    }
}