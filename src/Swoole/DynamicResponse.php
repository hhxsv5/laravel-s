<?php

namespace Hhxsv5\LaravelS\Swoole;

class DynamicResponse extends Response
{
    const CHUNK_LIMIT = 2097152; // 2M

    /**
     * @throws \Exception
     */
    public function gzip()
    {
        if (extension_loaded('zlib')) {
            $this->swooleResponse->gzip(2);
        } else {
            throw new \Exception('Http GZIP requires library "zlib", use "php --ri zlib" to check.');
        }
    }

    public function sendContent()
    {
        $content = $this->laravelResponse->getContent();

        $len = strlen($content);
        if ($len === 0) {
            $this->swooleResponse->end();
            return;
        }

        if ($len > self::CHUNK_LIMIT) {
            for ($i = 0, $limit = 1024 * 1024; $i < $len; $i += $limit) {
                $chunk = substr($content, $i, $limit);
                $this->swooleResponse->write($chunk);
            }
            $this->swooleResponse->end();
        } else {
            $this->swooleResponse->end($content);
        }
    }
}