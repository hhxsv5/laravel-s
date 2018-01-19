<?php

namespace Hhxsv5\LaravelS;


/**
 * Swoole Request => Laravel Request
 * Laravel Request => Laravel handle => Laravel Response
 * Laravel Response => Swoole Request
 */
class LaravelS
{
    protected static $s;

    /**
     * @var Laravel\Laravel
     */
    protected $laravel;
    protected $server;

    private function __construct(array $svrConf, array $laravelConf)
    {
        $this->server = new HttpServer($svrConf);
        $this->laravel = new Laravel\Laravel($laravelConf);
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
        $this->server->run($this->laravel);
    }

    public function reload()
    {

    }

    public function __destruct()
    {

    }

    public function &getLaravel()
    {
        return $this->laravel;
    }

    public function &getServer()
    {
        return $this->server;
    }

    public static function getInstance(array $svrConf = [], array $laravelConf = [])
    {
        if (self::$s === null) {
            self::$s = new self($svrConf, $laravelConf);
        }
        return self::$s;
    }
}