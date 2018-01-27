<?php

namespace Hhxsv5\LaravelS\Swoole;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class StaticResponse extends Response
{
    public function __construct(\swoole_http_response $swooleResponse, SymfonyResponse $laravelResponse)
    {
        parent::__construct($swooleResponse, $laravelResponse);
    }

    public function sendContent()
    {
        if ($this->laravelResponse->getStatusCode() == SymfonyResponse::HTTP_NOT_MODIFIED) {
            $this->swooleResponse->end();
        } else {
            /**
             * @var File $file
             */
            $file = $this->laravelResponse->getFile();
            $path = $file->getRealPath();
            if (filesize($path) > 0) {
                $this->swooleResponse->header('Content-Type', $file->getMimeType());
                $this->swooleResponse->sendfile($path);
            } else {
                $this->swooleResponse->end();
            }
        }
    }
}