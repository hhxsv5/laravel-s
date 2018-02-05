<?php

namespace Hhxsv5\LaravelS\Clients\Coroutine;

use Hhxsv5\LaravelS\Clients\Base;
use Swoole\Coroutine\Http\Client as SwooleHttpClient;
use Swoole\Coroutine;

class HTTP extends Base
{
    public function __construct($url, $timeout = 5)
    {
        $host = parse_url($url, PHP_URL_HOST);
        $port = parse_url($url, PHP_URL_PORT) ?: 80;

        if (version_compare(\swoole_version(), '1.9.24', '<')) {
            swoole_async_dns_lookup($host, function ($host, $ip) use ($port) {
                $this->cli = new SwooleHttpClient($ip, $port);
            });
        } else {
            $this->cli = new SwooleHttpClient($host, $port);
        }

        $this->cli->setHeaders([
            'Host' => $host,
        ]);
        $this->cli->set(['timeout' => $timeout]);
    }

    public function __call($name, $arguments)
    {
        return Coroutine::call_user_func_array([$this->cli, $name], $arguments);
    }

}