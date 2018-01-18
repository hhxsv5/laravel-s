<?php

namespace Hhxsv5\LaravelS;


/**
 * Swoole Request => Laravel Request
 * Laravel Request => Laravel handle => Laravel Response
 * Laravel Response => Swoole Request
 */
class LaravelS
{
    protected $laravel;
    protected $server;

    public function __construct()
    {
        $this->server = new HttpServer();
        $this->laravel = new Laravel\Laravel();
    }

    public function run()
    {
        $this->server->run($this);

        $this->laravel->run($this);
    }
}