<?php

namespace Hhxsv5\LaravelS;

use Hhxsv5\LaravelS\Illuminate\Laravel;
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

    private function __construct(array $laravelConf, array $svrConf)
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

    public static function getInstance(array $laravelConf = [], array $svrConf = [])
    {
        if (self::$s === null) {
            self::$s = new static($laravelConf, $svrConf);
        }
        return self::$s;
    }
}