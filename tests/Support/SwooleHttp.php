<?php

namespace Swoole\Http;

// Minimal mock targets for environments without the native Swoole extension.
class Request
{
    public $server;
    public $header;
    public $get;
    public $post;
    public $cookie;
    public $files;

    public function rawContent() {}
}

class Response
{
    public function status($code, $reason = '') {}
    public function header($name, $value, $format = true) {}
    public function trailer($name, $value) {}
    public function cookie($name, $value = '', $expires = 0, $path = '/', $domain = '', $secure = false, $httpOnly = false, $sameSite = '') {}
    public function rawcookie($name, $value = '', $expires = 0, $path = '/', $domain = '', $secure = false, $httpOnly = false, $sameSite = '') {}
    public function write($content) {}
    public function end($content = null) {}
    public function sendfile($path, $offset = 0, $length = 0) {}
    public function gzip($level) {}
}
