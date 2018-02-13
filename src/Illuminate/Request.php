<?php

namespace Hhxsv5\LaravelS\Illuminate;

use Illuminate\Http\Request as IlluminateRequest;

class Request extends IlluminateRequest
{
    public function setContent($content)
    {
        $this->content = $content;
    }
}