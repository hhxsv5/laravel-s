<?php

namespace Hhxsv5\LaravelS;

use Illuminate\Http\Response as LaravelResponse;

class SwooleResponse
{
    public function __construct()
    {
    }

    public static function fromLaravelResponse(LaravelResponse $response)
    {
        return new self();
    }
}