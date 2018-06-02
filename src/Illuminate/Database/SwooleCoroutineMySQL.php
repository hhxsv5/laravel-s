<?php

namespace Hhxsv5\LaravelS\Illuminate\Database;

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
        return new SwooleCoroutineMySQLStatement($oldStatement);
    }
}