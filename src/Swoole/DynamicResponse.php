<?php

namespace Hhxsv5\LaravelS\Swoole;

class DynamicResponse extends Response
{
    /**
     * @throws \Exception
     */
    public function gzip()
    {
        if (extension_loaded('zlib')) {
            $this->swooleResponse->gzip(4);
        } else {
            throw new \Exception('Http GZIP requires library "zlib", use "php --ri zlib" to check.');
        }
    }

    public function sendContent()
    {
        $content = $this->laravelResponse->getContent();
        $this->swooleResponse->end($content);
    }
}