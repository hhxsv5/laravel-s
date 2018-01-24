<?php

namespace Hhxsv5\LaravelS\Swoole;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class StaticResponse extends Response
{
    /**
     * @var File $file
     */
    protected $file;

    public function __construct(\swoole_http_response $swooleResponse, SymfonyResponse $laravelResponse)
    {
        parent::__construct($swooleResponse, $laravelResponse);
        $this->file = $this->laravelResponse->getFile();
    }

    public function sendContent()
    {
        $path = $this->file->getRealPath();
        if (filesize($path) > 0) {
            $this->swooleResponse->header('Content-Type', $this->file->getMimeType());
            $this->swooleResponse->sendfile($path);
        } else {
            $this->swooleResponse->end('');
        }
    }
}