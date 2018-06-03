<?php

namespace Hhxsv5\LaravelS\Illuminate\Database;

use Swoole\Coroutine\MySQL\Statement as SwooleStatement;

class CoroutineMySQLStatement
{
    protected $statement;
    protected $bindParams = [];
    protected $result;

    public function __construct(SwooleStatement $statement)
    {
        $this->statement = $statement;
    }

    public function rowCount()
    {
        return $this->statement->affected_rows;
    }

    public function bindValue($parameter, $value)
    {
        $this->bindParams[$parameter] = $value;
    }

    /**
     * @param array $params
     * @param int $timeout
     * @return bool
     * @throws StatementException
     */
    public function execute(array $params = [], $timeout = -1)
    {
        if (empty($params) && !empty($this->bindParams)) {
            $params = $this->bindParams;
        }
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