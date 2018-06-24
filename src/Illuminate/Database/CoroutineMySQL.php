<?php

namespace Hhxsv5\LaravelS\Illuminate\Database;

use Illuminate\Database\QueryException;
use Swoole\Coroutine\MySQL as SwooleMySQL;

class CoroutineMySQL extends \PDO
{
    protected $coMySQL;

    public function __construct()
    {
        $this->coMySQL = new SwooleMySQL();
    }

    public function connect(array $serverInfo)
    {
        $this->coMySQL->connect($serverInfo);
    }

    public function prepare($statement, $options = null)
    {
        $oldStatement = $this->coMySQL->prepare($statement);
        if ($oldStatement === false) {
            throw new QueryException($statement, [], new \Exception($this->coMySQL->error, $this->coMySQL->errno));
        }
        return new CoroutineMySQLStatement($oldStatement);
    }

    public function beginTransaction()
    {
        return $this->coMySQL->begin();
    }

    public function commit()
    {
        return $this->coMySQL->commit();
    }

    public function rollBack()
    {
        return $this->coMySQL->rollback();
    }

    public function query($statement, $mode = \PDO::ATTR_DEFAULT_FETCH_MODE, $arg3 = null, array $ctorargs = [])
    {
        return $this->coMySQL->query($statement, array_get($ctorargs, 'timeout', 0.0));
    }

    public function lastInsertId($name = null)
    {
        return $this->coMySQL->insert_id;
    }

    public function rowCount()
    {
        return $this->coMySQL->affected_rows;
    }

    public function quote($string, $parameter_type = \PDO::PARAM_STR)
    {
        //TODO
        return $string;
    }
}
