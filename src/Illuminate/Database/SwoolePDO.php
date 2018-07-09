<?php

namespace Hhxsv5\LaravelS\Illuminate\Database;

use Illuminate\Database\QueryException;
use Swoole\Coroutine\MySQL as SwooleMySQL;

class SwoolePDO extends \PDO
{
    protected $sm;
    protected $isInTransaction = false;

    public function __construct()
    {
        $this->sm = new SwooleMySQL();
    }

    /**
     * @param array $serverInfo
     * @throws ConnectionException
     */
    public function connect(array $serverInfo)
    {
        $this->sm->connect($serverInfo);

        if ($this->sm->connected === false) {
            $msg = sprintf('Cannot connect to the database: %s',
                $this->sm->connect_errno ? $this->sm->connect_error : $this->sm->error
            );
            $code = $this->sm->connect_errno ?: $this->sm->errno;
            throw new ConnectionException($msg, $code);
        }

    }

    public function prepare($statement, $options = null)
    {
        $swStatement = $this->sm->prepare($statement);
        if ($swStatement === false) {
            throw new QueryException($statement, [], new StatementException($this->sm->error, $this->sm->errno));
        }
        return new SwoolePDOStatement($swStatement);
    }

    public function beginTransaction()
    {
        $this->isInTransaction = true;
        $this->sm->begin();
    }

    public function commit()
    {
        $this->sm->commit();
        $this->isInTransaction = false;
    }

    public function rollBack()
    {
        $this->sm->rollback();
        $this->isInTransaction = false;
    }

    public function query($statement, $mode = \PDO::ATTR_DEFAULT_FETCH_MODE, $arg3 = null, array $ctorargs = [])
    {
        $result = $this->sm->query($statement, array_get($ctorargs, 'timeout', 0.0));
        if ($result === false) {
            throw new QueryException($statement, [], new \Exception($this->sm->error, $this->sm->errno));
        }
        return $result;
    }

    public function exec($statement)
    {
        return $this->query($statement);
    }

    public function lastInsertId($name = null)
    {
        return $this->sm->insert_id;
    }

    public function rowCount()
    {
        return $this->sm->affected_rows;
    }

    public function quote($string, $parameter_type = \PDO::PARAM_STR)
    {
        //TODO
        return $string;
    }

    public function errorCode()
    {
        return $this->sm->errno;
    }

    public function errorInfo()
    {
        return [
            $this->sm->errno,
            $this->sm->errno,
            $this->sm->error,
        ];
    }

    public function inTransaction()
    {
        return $this->isInTransaction;
    }

    public function getAttribute($attribute)
    {
        return isset($this->sm->serverInfo[$attribute]) ? $this->sm->serverInfo[$attribute] : null;
    }

    public function setAttribute($attribute, $value)
    {
        // TODO
        return false;
    }

    public static function getAvailableDrivers()
    {
        return ['mysql'];
    }

    public function __destruct()
    {
        $this->sm->close();
    }
}
