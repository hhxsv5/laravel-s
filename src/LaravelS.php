<?php

namespace Hhxsv5\LaravelS;

use Hhxsv5\LaravelS\Laravel\Laravel;
use Hhxsv5\LaravelS\Swoole\Server;


/**
 * Swoole Request => Laravel Request
 * Laravel Request => Laravel handle => Laravel Response
 * Laravel Response => Swoole Request
 */
class LaravelS
{
    protected static $s;

    protected $server;

    private function __construct(array $svrConf, array $laravelConf)
    {
        $laravel = new Laravel($laravelConf);
        $this->server = new Server($svrConf, $laravel);
    }

    private function __clone()
    {

    }

    private function __sleep()
    {
        return [];
    }

    public function __wakeup()
    {
        self::$s = $this;
    }

    public function run()
    {
        $this->server->run();
    }

    public function reload()
    {

    }

    public function __destruct()
    {

    }

    public static function getInstance(array $svrConf = [], array $laravelConf = [])
    {
        if (self::$s === null) {
            self::$s = new self($svrConf, $laravelConf);
        }
        return self::$s;
    }
}