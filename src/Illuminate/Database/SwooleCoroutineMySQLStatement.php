<?php

namespace Hhxsv5\LaravelS\Illuminate\Database;

use Swoole\Coroutine\MySQL\Statement as CoroutineMySQLStatement;

class SwooleCoroutineMySQLStatement
{
    protected $statement;
    protected $result;

    public function __construct(CoroutineMySQLStatement $statement)
    {
        $this->statement = $statement;
    }

    public function rowCount()
    {
        return $this->statement->affected_rows;
    }

    public function execute(array $params = [], $timeout = -1)
    {
        $this->result = $this->statement->execute($params, $timeout);
    }

    public function fetchAll()
    {
        return $this->result;
    }
}