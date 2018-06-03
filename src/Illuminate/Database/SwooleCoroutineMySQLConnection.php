<?php

namespace Hhxsv5\LaravelS\Illuminate\Database;

use Illuminate\Database\MySqlConnection;

class SwooleCoroutineMySQLConnection extends MySqlConnection
{
    /**
     * The active swoole mysql connection.
     *
     * @var \Swoole\Coroutine\MySQL
     */
    protected $pdo;

    /**
     * The active swoole mysql used for reads.
     *
     * @var \Swoole\Coroutine\MySQL
     */
    protected $readPdo;

    public function __construct($pdo, $database = '', $tablePrefix = '', array $config = [])
    {
        $this->pdo = $pdo;
        $this->database = $database;
        $this->tablePrefix = $tablePrefix;
        $this->config = $config;
        $this->useDefaultQueryGrammar();
        $this->useDefaultPostProcessor();
    }

    public function getDriverName()
    {
        return 'Swoole Coroutine MySQL';
    }

}