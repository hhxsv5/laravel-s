<?php

namespace Hhxsv5\LaravelS\Illuminate\Database;

use Swoole\Coroutine\MySQL\Statement as SwooleStatement;

class SwoolePDOStatement extends \PDOStatement
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

    public function bindValue($parameter, $value, $data_type = \PDO::PARAM_STR)
    {
        $this->bindParams[$parameter] = $value;
    }

    /**
     * @param array $input_parameters
     * @return bool
     * @throws StatementException
     */
    public function execute($input_parameters = null)
    {
        if (empty($input_parameters) && !empty($this->bindParams)) {
            $input_parameters = $this->bindParams;
        }
        $input_parameters = (array)$input_parameters;
        $this->result = $this->statement->execute($input_parameters, array_get($input_parameters, '__timeout__', -1));
        if ($this->statement->errno != 0) {
            throw new StatementException($this->statement->error, $this->statement->errno);
        }
        return true;
    }

    public function fetchAll($how = null, $class_name = null, $ctor_args = null)
    {
        return $this->result;
    }
}