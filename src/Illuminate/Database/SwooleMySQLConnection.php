<?php

namespace Hhxsv5\LaravelS\Illuminate\Database;

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
}