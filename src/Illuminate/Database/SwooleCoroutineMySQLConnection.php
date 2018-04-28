<?php

namespace Hhxsv5\LaravelS\Illuminate\Database;

use Illuminate\Database\Connection;
use Illuminate\Database\QueryException;

class SwooleCoroutineMySQLConnection extends Connection
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

    public function select($query, $bindings = [], $useReadPdo = true)
    {
        return $this->run($query, $bindings, function ($me, $query, $bindings) use ($useReadPdo) {
            if ($me->pretending()) {
                return [];
            }

            $db = $this->getPdoForSelect($useReadPdo);
            $query = 'SELECT count(*) AS AGGREGATE FROM doctor';

            $statement = $db->prepare($query);
            if ($statement === false) {
                throw new QueryException($query, $bindings, new \Exception($db->error, $db->errno));
            }

            var_dump('before execute');
            $result = $statement->execute($me->prepareBindings($bindings));
            var_dump('after execute');

            return $result;
        });
    }

    public function __construct($pdo, $database = '', $tablePrefix = '', array $config = [])
    {
        $this->pdo = $pdo;

        $this->database = $database;

        $this->tablePrefix = $tablePrefix;

        $this->config = $config;

        $this->useDefaultQueryGrammar();

        $this->useDefaultPostProcessor();
    }

    public function beginTransaction()
    {
        if ($this->transactions == 0) {
            try {
                $this->pdo->query('BEGIN');
            } catch (\Exception $e) {
                if ($this->causedByLostConnection($e)) {
                    $this->reconnect();
                    $this->pdo->query('BEGIN');
                } else {
                    throw $e;
                }
            }
        } elseif ($this->transactions >= 1 && $this->queryGrammar->supportsSavepoints()) {
            $this->pdo->query(
                $this->queryGrammar->compileSavepoint('trans' . ($this->transactions + 1))
            );
        }

        ++$this->transactions;

        $this->fireConnectionEvent('beganTransaction');
    }

    public function commit()
    {
        if ($this->transactions == 1) {
            $this->pdo->query('COMMIT');
        }

        --$this->transactions;

        $this->fireConnectionEvent('committed');
    }

    public function rollBack()
    {
        if ($this->transactions == 1) {
            $this->pdo->query('ROLLBACK');
        } elseif ($this->transactions > 1 && $this->queryGrammar->supportsSavepoints()) {
            $this->pdo->query(
                $this->queryGrammar->compileSavepointRollBack('trans' . $this->transactions)
            );
        }

        $this->transactions = max(0, $this->transactions - 1);

        $this->fireConnectionEvent('rollingBack');
    }

    public function getDriverName()
    {
        return 'Swoole Coroutine MySQL';
    }

}