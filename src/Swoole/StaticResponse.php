<?php

namespace Hhxsv5\LaravelS\Swoole;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class StaticResponse extends Response
{
    protected $file;

    public function __construct(\swoole_http_response $swooleResponse, SymfonyResponse $laravelResponse)
    {
        parent::__construct($swooleResponse, $laravelResponse);
        $this->file = $this->laravelResponse->getFile();
    }

    public function sendContent()
    {
        if (filesize($this->file) > 0) {
            $this->swooleResponse->sendfile($this->file);
        } else {
            $this->swooleResponse->end('');
        }
    }
}