<?php

namespace Hhxsv5\LaravelS\Swoole;

interface ResponseInterface
{
    public function sendStatusCode();

    public function sendHeaders();

    public function sendCookies();

    public function gzip();

    public function sendContent();

    public function send($gzip = false);
}