<?php

namespace Hhxsv5\LaravelS\Swoole;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;

class StaticResponse extends Response
{
    /**
     * @var BinaryFileResponse $laravelResponse
     */
    protected $laravelResponse;

    public function gzip()
    {

    }

    /**
     * @throws \Exception
     */
    public function sendContent()
    {
        /**
         * @var File $file
         */
        $file = $this->laravelResponse->getFile();
        $this->swooleResponse->header('Content-Type', $file->getMimeType());
        if ($this->laravelResponse->getStatusCode() == BinaryFileResponse::HTTP_NOT_MODIFIED) {
            $this->swooleResponse->end();
        } else {
            $path = $file->getRealPath();
            if (filesize($path) > 0) {
                if (version_compare(\swoole_version(), '1.7.21', '<')) {
                    throw new \Exception('sendfile() require Swoole >= 1.7.21');
                }
                $this->swooleResponse->sendfile($path);
            } else {
                $this->swooleResponse->end();
            }
        }
    }
}