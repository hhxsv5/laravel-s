<?php

namespace Hhxsv5\LaravelS;


use Symfony\Component\HttpFoundation\Response as LaravelResponse;

class SwooleResponse
{
    protected $swooleResponse;
    protected $laravelResponse;

    public function __construct(\swoole_http_response $swooleResponse, LaravelResponse $laravelResponse)
    {
        $this->swooleResponse = $swooleResponse;
        $this->laravelResponse = $laravelResponse;
    }

    public function send($acceptGzip = true)
    {
        // status
        $this->swooleResponse->status($this->laravelResponse->getStatusCode());

        // headers
        $this->swooleResponse->header('Server', 'LaravelS');
        foreach ($this->laravelResponse->headers->allPreserveCase() as $name => $values) {
            foreach ($values as $value) {
                $this->swooleResponse->header($name, $value);
            }
        }

        // cookies
        foreach ($this->laravelResponse->headers->getCookies() as $cookie) {
            $this->swooleResponse->cookie(
                $cookie->getName(),
                urlencode($cookie->getValue()),
                $cookie->getExpiresTime(),
                $cookie->getPath(),
                $cookie->getDomain(),
                $cookie->isSecure(),
                $cookie->isHttpOnly()
            );
        }

        // check gzip
        if ($acceptGzip) {
            //TODO
        }

        // content
        $content = $this->laravelResponse->getContent();
        $this->swooleResponse->end($content);
    }
}