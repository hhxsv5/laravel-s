<?php

namespace Hhxsv5\LaravelS\Swoole\Traits;

trait LogTrait
{
    public function logException(\Exception $e)
    {
        $this->log(
            sprintf(
                'Uncaught exception \'%s\': [%d]%s called in %s:%d%s%s',
                get_class($e),
                $e->getCode(),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                PHP_EOL,
                $e->getTraceAsString()
            ),
            'ERROR'
        );
    }

    public function log($msg, $type = 'INFO')
    {
        echo sprintf('[%s] [%s] LaravelS: %s', date('Y-m-d H:i:s'), $type, $msg), PHP_EOL;
    }

    public function callWithCatchException(callable $callback)
    {
        try {
            return $callback();
        } catch (\Exception $e) {
            $this->logException($e);
            return false;
        }
    }
}