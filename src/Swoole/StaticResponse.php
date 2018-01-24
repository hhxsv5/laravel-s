<?php

namespace Hhxsv5\LaravelS\Swoole;

class StaticResponse extends Response
{
    public function sendContent()
    {
        $file = $this->laravelResponse->getFile();
        if (filesize($file) > 0) {
            $this->swooleResponse->sendfile($file);
        } else {
            $this->swooleResponse->end('');
        }
    }
}