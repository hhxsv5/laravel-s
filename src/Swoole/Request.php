<?php

namespace Hhxsv5\LaravelS\Swoole;

use Illuminate\Http\Request as IlluminateRequest;
use Swoole\Http\Request as SwooleRequest;
use Symfony\Component\HttpFoundation\ParameterBag;

class Request
{
    protected $swooleRequest;

    public function __construct(SwooleRequest $request)
    {
        $this->swooleRequest = $request;
    }

    /**
     * Convert SwooleRequest to IlluminateRequest
     * @param array $rawServer
     * @param array $rawEnv
     * @return IlluminateRequest
     */
    public function toIlluminateRequest(array $rawServer = [], array $rawEnv = [])
    {
        $__GET = isset($this->swooleRequest->get) ? $this->swooleRequest->get : [];
        $__POST = isset($this->swooleRequest->post) ? $this->swooleRequest->post : [];
        $__COOKIE = isset($this->swooleRequest->cookie) ? $this->swooleRequest->cookie : [];
        $server = isset($this->swooleRequest->server) ? $this->swooleRequest->server : [];
        $headers = isset($this->swooleRequest->header) ? $this->swooleRequest->header : [];
        $__FILES = isset($this->swooleRequest->files) ? $this->swooleRequest->files : [];
        $__CONTENT = empty($__FILES) ? $this->swooleRequest->rawContent() : ''; // Cannot call rawContent() to avoid double the file memory when uploading a file.
        $_REQUEST = [];
        $_SESSION = [];

        static $headerServerMapping = [
            'x-real-ip'       => 'REMOTE_ADDR',
            'x-real-port'     => 'REMOTE_PORT',
            'server-protocol' => 'SERVER_PROTOCOL',
            'server-name'     => 'SERVER_NAME',
            'server-addr'     => 'SERVER_ADDR',
            'server-port'     => 'SERVER_PORT',
            'scheme'          => 'REQUEST_SCHEME',
        ];

        $_ENV = $rawEnv;
        $_SERVER = $rawServer;
        foreach ($headers as $key => $value) {
            // Fix client && server's info
            if (isset($headerServerMapping[$key])) {
                $server[$headerServerMapping[$key]] = $value;
            } else {
                $key = str_replace('-', '_', $key);
                $server['http_' . $key] = $value;
            }
        }
        $server = array_change_key_case($server, CASE_UPPER);
        $_SERVER = array_merge($_SERVER, $server);
        if (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] === 'https') {
            $_SERVER['HTTPS'] = 'on';
        }

        // Fix REQUEST_URI with QUERY_STRING
        if (strpos($_SERVER['REQUEST_URI'], '?') === false
            && isset($_SERVER['QUERY_STRING'])
            && $_SERVER['QUERY_STRING'] !== ''
        ) {
            $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
        }

        // Fix argv & argc
        if (!isset($_SERVER['argv'])) {
            $_SERVER['argv'] = isset($GLOBALS['argv']) ? $GLOBALS['argv'] : [];
            $_SERVER['argc'] = isset($GLOBALS['argc']) ? $GLOBALS['argc'] : 0;
        }

        // Initialize laravel request
        IlluminateRequest::enableHttpMethodParameterOverride();
        $request = IlluminateRequest::createFromBase(new \Symfony\Component\HttpFoundation\Request($__GET, $__POST, [], $__COOKIE, $__FILES, $_SERVER, $__CONTENT));

        if (0 === strpos($request->headers->get('CONTENT_TYPE'), 'application/x-www-form-urlencoded')
            && in_array(strtoupper($request->server->get('REQUEST_METHOD', 'GET')), ['PUT', 'DELETE', 'PATCH'])
        ) {
            parse_str($request->getContent(), $data);
            $request->request = new ParameterBag($data);
        }

        return $request;
    }
}
