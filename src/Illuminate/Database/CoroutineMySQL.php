<?php

namespace Hhxsv5\LaravelS\Illuminate\Database;

use Illuminate\Database\QueryException;
use Swoole\Coroutine\MySQL as SwooleMySQL;

class CoroutineMySQL
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

    public function prepare($sql)
    {
        $oldStatement = $this->coMySQL->prepare($sql);
        if ($oldStatement === false) {
            throw new QueryException($sql, [], new \Exception($this->coMySQL->error, $this->coMySQL->errno));
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

    public function query($sql, $timeout = 0.0)
    {
        return $this->coMySQL->query($sql, $timeout);
    }

    public function lastInsertId()
    {
        return $this->coMySQL->insert_id;
    }

    public function rowCount()
    {
        return $this->coMySQL->affected_rows;
    }

    public function quote($string)
    {
        //TODO
        return $string;
    }
}