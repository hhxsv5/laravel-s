<?php

namespace Hhxsv5\LaravelS\Illuminate\Database;

use Illuminate\Database\QueryException;
use Swoole\Coroutine\MySQL as CoroutineMySQL;

class SwooleCoroutineMySQL extends CoroutineMySQL
{
    public function lastInsertId()
    {
        return $this->insert_id;
    }

    public function prepare($sql)
    {
        $oldStatement = parent::prepare($sql);
        if ($oldStatement === false) {
            throw new QueryException($sql, [], new \Exception($this->error, $this->errno));
        }
        return new SwooleCoroutineMySQLStatement($oldStatement);
    }
}