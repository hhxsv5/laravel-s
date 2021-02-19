<?php

namespace Hhxsv5\LaravelS\Swoole;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StaticResponse extends Response
{
    /**@var BinaryFileResponse */
    protected $laravelResponse;

    public function gzip()
    {
    }

    /**
     * @throws \Exception
     */
    public function sendContent()
    {
        $file = $this->laravelResponse->getFile();
        $this->swooleResponse->header('Content-Type', $file->getMimeType());
        if ($this->laravelResponse->getStatusCode() == BinaryFileResponse::HTTP_NOT_MODIFIED) {
            $this->swooleResponse->end();
            return;
        }

        $path = $file->getPathname();
        $size = filesize($path);
        if ($size <= 0) {
            $this->swooleResponse->end();
            return;
        }

        // Support deleteFileAfterSend: https://github.com/symfony/http-foundation/blob/5.0/BinaryFileResponse.php#L305
        $reflection = new \ReflectionObject($this->laravelResponse);
        if ($reflection->hasProperty('deleteFileAfterSend')) {
            $deleteFileAfterSend = $reflection->getProperty('deleteFileAfterSend');
            $deleteFileAfterSend->setAccessible(true);
            $deleteFile = $deleteFileAfterSend->getValue($this->laravelResponse);
        } else {
            $deleteFile = false;
        }

        if ($deleteFile) {
            $fp = fopen($path, 'rb');

            for ($offset = 0, $limit = (int)(0.99 * $this->chunkLimit); $offset < $size; $offset += $limit) {
                fseek($fp, $offset, SEEK_SET);
                $chunk = fread($fp, $limit);
                $this->swooleResponse->write($chunk);
            }
            $this->swooleResponse->end();

            fclose($fp);

            if (file_exists($path)) {
                unlink($path);
            }
        } else {
            if (version_compare(SWOOLE_VERSION, '1.7.21', '<')) {
                throw new \RuntimeException('sendfile() require Swoole >= 1.7.21');
            }
            $this->swooleResponse->sendfile($path);
        }
    }
}