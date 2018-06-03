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

    /**
     * @param array $params
     * @param int $timeout
     * @return bool
     * @throws StatementException
     */
    public function execute(array $params = [], $timeout = -1)
    {
        $this->result = $this->statement->execute($params, $timeout);
        if ($this->statement->errno != 0) {
            throw new StatementException($this->statement->error, $this->statement->errno);
        }
        return true;
    }

    public function fetchAll()
    {
        return $this->result;
    }
}