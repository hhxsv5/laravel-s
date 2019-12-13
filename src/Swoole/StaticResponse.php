<?php

namespace Hhxsv5\LaravelS\Swoole;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;

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
        /**@var File $file */
        $file = $this->laravelResponse->getFile();
        $this->swooleResponse->header('Content-Type', $file->getMimeType());
        if ($this->laravelResponse->getStatusCode() == BinaryFileResponse::HTTP_NOT_MODIFIED) {
            $this->swooleResponse->end();
        } else {
            $path = $file->getPathname();
            $size = filesize($path);
            if ($size > 0) {
                if (version_compare(swoole_version(), '1.7.21', '<')) {
                    throw new \RuntimeException('sendfile() require Swoole >= 1.7.21');
                }

                // Support deleteFileAfterSend: https://github.com/symfony/http-foundation/blob/5.0/BinaryFileResponse.php#L305
                $reflection = new \ReflectionObject($this->laravelResponse);
                try {
                    $deleteFileAfterSend = $reflection->getProperty('deleteFileAfterSend');
                    $deleteFileAfterSend->setAccessible(true);
                    $deleteFile = $deleteFileAfterSend->getValue($this->laravelResponse);
                } catch (\Exception $e) {
                    $deleteFile = false;
                }

                if ($deleteFile) {
                    $fp = fopen($path, 'rb');

                    for ($offset = 0, $limit = floor(1.99 * 1024 * 1024); $offset < $size; $offset += $limit) {
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
                    $this->swooleResponse->sendfile($path);
                }
            } else {
                $this->swooleResponse->end();
            }
        }
    }
}