<?php

namespace Hhxsv5\LaravelS\Illuminate\Database;


use Exception;
use Illuminate\Support\Arr;
use Swoole\Coroutine\MySQL as CoroutineMySQL;
use Illuminate\Database\Connectors\Connector;
use Illuminate\Database\Connectors\ConnectorInterface;

class SwooleCoroutineMySQLConnector extends Connector implements ConnectorInterface
{

    /**
     * @param string $dsn
     * @param array $config
     * @param array $options
     * @return \PDO|CoroutineMySQL
     * @throws Exception
     */
    public function createConnection($dsn, array $config, array $options)
    {
        $username = Arr::get($config, 'username');

        $password = Arr::get($config, 'password');

        try {
            $pdo = $this->connect($config);
        } catch (\Exception $e) {
            $pdo = $this->tryAgainIfCausedByLostConnection(
                $e, $dsn, $username, $password, $config
            );
        }

        return $pdo;
    }

    protected function tryAgainIfCausedByLostConnection(Exception $e, $dsn, $username, $password, $options)
    {
        if ($this->causedByLostConnection($e)) {
            return $this->connect($options);
        }

        throw $e;
    }

    public function connect(array $config)
    {
        $connection = new CoroutineMySQL();
        $connection->connect([
            'host'        => Arr::get($config, 'host', '127.0.0.1'),
            'port'        => Arr::get($config, 'port', 3306),
            'user'        => Arr::get($config, 'username', 'hhxsv5'),
            'password'    => Arr::get($config, 'password', '52100'),
            'database'    => Arr::get($config, 'database', 'test'),
            'timeout'     => Arr::get($config, 'timeout', 5),
            'charset'     => Arr::get($config, 'charset', 'utf8mb4'),
            'strict_type' => Arr::get($config, 'strict', false),
        ]);
        if (isset($config['timezone'])) {
            $connection->prepare(
                'set time_zone="' . $config['timezone'] . '"'
            )->execute();
        }
        if (isset($config['strict'])) {
            if ($config['strict']) {
                $connection->prepare("set session sql_mode='STRICT_ALL_TABLES'")->execute();
            } else {
                $connection->prepare("set session sql_mode='ANSI_QUOTES'")->execute();
            }
        }
        return $connection;
    }
}