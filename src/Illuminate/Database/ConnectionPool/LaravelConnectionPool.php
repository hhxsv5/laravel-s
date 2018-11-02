<?php

namespace Hhxsv5\LaravelS\Illuminate\Database\ConnectionPool;

use Illuminate\Contracts\Container\Container;

final class LaravelConnectionPool extends ConnectionPool
{
    protected $container;

    public function __construct($minActive, $maxActive, Container $container)
    {
        parent::__construct($minActive, $maxActive);
        $this->container = $container;
    }
}