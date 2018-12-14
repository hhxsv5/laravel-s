<?php

namespace Hhxsv5\LaravelS\Swoole\Traits;

use Hhxsv5\LaravelS\LaravelS;

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
        $outputStyle = LaravelS::getOutputStyle();
        if ($outputStyle) {
            $msg = sprintf('[%s] %s', date('Y-m-d H:i:s'), $msg);
            switch (strtolower($type)) {
                case 'WARN':
                    $outputStyle->warning($msg);
                    break;
                case 'ERROR':
                    $outputStyle->error($msg);
                    break;
                default:
                    $outputStyle->note($msg);
                    break;
            }
        } else {
            $msg = sprintf('[%s] [%s] LaravelS: %s', date('Y-m-d H:i:s'), $type, $msg . PHP_EOL);
            echo $msg;
        }
    }

    public function info($msg)
    {
        $this->log($msg, 'INFO');
    }

    public function warning($msg)
    {
        $this->log($msg, 'WARN');
    }

    public function error($msg)
    {
        $this->log($msg, 'ERROR');
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