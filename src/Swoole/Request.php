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

    /**
     * @return IlluminateRequest
     */
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
        // Fix client real-ip
        if (isset($this->swooleRequest->header['x-real-ip'])) {
            $_SERVER['REMOTE_ADDR'] = (string)$this->swooleRequest->header['x-real-ip'];
        }
        // Fix client real-port
        if (isset($this->swooleRequest->header['x-real-port'])) {
            $_SERVER['REMOTE_PORT'] = (int)$this->swooleRequest->header['x-real-port'];
        }
        $_SERVER = array_change_key_case($_SERVER, CASE_UPPER);

        // Fix argv & argc
        if (!isset($_SERVER['argv'])) {
            $_SERVER['argv'] = isset($GLOBALS['argv']) ? $GLOBALS['argv'] : [];
            $_SERVER['argc'] = isset($GLOBALS['argc']) ? $GLOBALS['argc'] : 0;
        }

        $requests = ['C' => $_COOKIE, 'G' => $_GET, 'P' => $_POST];
        $requestOrder = ini_get('request_order') ?: ini_get('variables_order');
        $requestOrder = preg_replace('#[^CGP]#', '', strtoupper($requestOrder)) ?: 'GP';
        foreach (str_split($requestOrder) as $order) {
            $_REQUEST = array_merge($_REQUEST, $requests[$order]);
        }

        $request = IlluminateRequest::capture();
        $reflection = new \ReflectionObject($request);
        $content = $reflection->getProperty('content');
        $content->setAccessible(true);
        $content->setValue($request, $this->swooleRequest->rawContent());

        return $request;
    }

}
