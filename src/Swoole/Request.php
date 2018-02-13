<?php

namespace Hhxsv5\LaravelS\Swoole;

use Illuminate\Http\Request as IlluminateRequest;

class Request
{
    protected $swooleRequest;

    public function __construct(\swoole_http_request $request)
    {
        $this->swooleRequest = $request;
    }

    public function toIlluminateRequest()
    {
        $_GET = isset($this->swooleRequest->get) ? $this->swooleRequest->get : [];
        $_POST = isset($this->swooleRequest->post) ? $this->swooleRequest->post : [];
        $_COOKIE = isset($this->swooleRequest->cookie) ? $this->swooleRequest->cookie : [];
        $_SERVER = isset($this->swooleRequest->server) ? $this->swooleRequest->server : [];
        $headers = isset($this->swooleRequest->header) ? $this->swooleRequest->header : [];
        $_FILES = isset($this->swooleRequest->files) ? $this->swooleRequest->files : [];
        $_ENV = [];
        $_REQUEST = [];

        foreach ($headers as $key => $value) {
            $key = str_replace('-', '_', $key);
            $_SERVER['http_' . $key] = $value;
        }
        $_SERVER = array_change_key_case($_SERVER, CASE_UPPER);

        $request = ['C' => $_COOKIE, 'G' => $_GET, 'P' => $_POST];
        $requestOrder = ini_get('request_order') ?: ini_get('variables_order');
        $requestOrder = preg_replace('#[^CGP]#', '', strtoupper($requestOrder)) ?: 'GP';
        foreach (str_split($requestOrder) as $order) {
            $_REQUEST = array_merge($_REQUEST, $request[$order]);
        }

        return IlluminateRequest::capture();
    }

}
