<?php

namespace Hhxsv5\LaravelS\Swoole;

class DynamicResponse extends Response
{
    public function sendContent()
    {
        $content = $this->laravelResponse->getContent();
        $this->swooleResponse->end($content);
    }
}