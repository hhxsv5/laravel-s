<?php

namespace Hhxsv5\LaravelS\Clients\Coroutine;

use Hhxsv5\LaravelS\Clients\Base;
use Swoole\Coroutine\Http\Client as SwooleHttpClient;
use Swoole\Coroutine;

class HTTP extends Base
{
    protected $path;

    public function __construct($url, $timeout = 5, array $headers = [])
    {
        $parses = parse_url($url);
        $host = $parses['host'];
        $port = isset($parses['port']) ? $parses['port'] : 80;
        $this->path = sprintf('%s%s%s',
            isset($parses['path']) ? $parses['path'] : '/',
            isset($parses['query']) ? ('?' . $parses['query']) : '',
            isset($parses['fragment']) ? ('#' . $parses['fragment']) : ''
        );

        if (version_compare(\swoole_version(), '1.9.24', '<')) {
            swoole_async_dns_lookup($host, function ($host, $ip) use ($port, $headers, $timeout) {
                $this->cli = new SwooleHttpClient($ip, $port);
                $this->cli->setHeaders($headers + ['Host' => $host]);
                $this->cli->set(['timeout' => $timeout]);
            });
        } else {
            $this->cli = new SwooleHttpClient($host, $port);
            $this->cli->setHeaders($headers + ['Host' => $host]);
            $this->cli->set(['timeout' => $timeout]);
        }
    }

    public function get()
    {
        $this->cli->get($this->path);
        return $this->cli->body;
    }

    public function __call($name, $arguments)
    {
        return Coroutine::call_user_func_array([$this->cli, $name], $arguments);
    }

}