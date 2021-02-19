<?php


namespace Hhxsv5\LaravelS\Swoole;

use Swoole\Http\Response as SwooleResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

abstract class Response implements ResponseInterface
{
    protected $chunkLimit = 2097152; // 2 * 1024 * 1024

    protected $swooleResponse;

    protected $laravelResponse;

    public function __construct(SwooleResponse $swooleResponse, SymfonyResponse $laravelResponse)
    {
        $this->swooleResponse = $swooleResponse;
        $this->laravelResponse = $laravelResponse;
    }

    public function setChunkLimit($chunkLimit)
    {
        $this->chunkLimit = $chunkLimit;
    }

    public function sendStatusCode()
    {
        $this->swooleResponse->status($this->laravelResponse->getStatusCode());
    }

    private function getHeaders()
    {
        if (method_exists($this->laravelResponse->headers, 'allPreserveCaseWithoutCookies')) {
            return $this->laravelResponse->headers->allPreserveCaseWithoutCookies();
        }

        return $this->laravelResponse->headers->allPreserveCase();
    }

    public function sendHeaders()
    {
        $headers = $this->getHeaders();
        $trailers = isset($headers['trailer']) ? $headers['trailer'] : [];

        foreach ($headers as $name => $values) {
            if (in_array($name, $trailers, true)) {
                continue;
            }
            if (version_compare(SWOOLE_VERSION, '4.6.0', '>=')) {
                $this->swooleResponse->header($name, $values);
            } else {
                foreach ($values as $value) {
                    $this->swooleResponse->header($name, $value);
                }
            }
        }
    }

    public function sendTrailers()
    {
        $headers = $this->getHeaders();
        $trailers = isset($headers['trailer']) ? $headers['trailer'] : [];

        foreach ($headers as $name => $values) {
            if (!in_array($name, $trailers, true)) {
                continue;
            }

            foreach ($values as $value) {
                $this->swooleResponse->trailer($name, $value);
            }
        }
    }

    public function sendCookies()
    {
        $hasIsRaw = null;
        /**@var \Symfony\Component\HttpFoundation\Cookie[] $cookies */
        $cookies = $this->laravelResponse->headers->getCookies();
        foreach ($cookies as $cookie) {
            if ($hasIsRaw === null) {
                $hasIsRaw = method_exists($cookie, 'isRaw');
            }
            $setCookie = $hasIsRaw && $cookie->isRaw() ? 'rawcookie' : 'cookie';
            $this->swooleResponse->$setCookie(
                $cookie->getName(),
                $cookie->getValue(),
                $cookie->getExpiresTime(),
                $cookie->getPath(),
                $cookie->getDomain(),
                $cookie->isSecure(),
                $cookie->isHttpOnly()
            );
        }
    }

    public function send($gzip = false)
    {
        $this->sendStatusCode();
        $this->sendHeaders();
        $this->sendCookies();
        $this->sendTrailers();
        if ($gzip) {
            $this->gzip();
        }
        $this->sendContent();
    }
}
