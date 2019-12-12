<?php

namespace Hhxsv5\LaravelS\Illuminate;

use Hhxsv5\LaravelS\LaravelS;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

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
        $msg = sprintf('[%s] [%s] %s', date('Y-m-d H:i:s'), $type, $msg);
        if ($outputStyle) {
            switch (strtoupper($type)) {
                case 'INFO':
                    $outputStyle->writeln("<info>$msg</info>");
                    break;
                case 'WARNING':
                    if (!$outputStyle->getFormatter()->hasStyle('warning')) {
                        $style = new OutputFormatterStyle('yellow');
                        $outputStyle->getFormatter()->setStyle('warning', $style);
                    }
                    $outputStyle->writeln("<warning>$msg</warning>");
                    break;
                case 'ERROR':
                    $outputStyle->writeln("<error>$msg</error>");
                    break;
                case 'TRACE':
                default:
                    $outputStyle->writeln($msg);
                    break;
            }
        } else {
            echo $msg, PHP_EOL;
        }
    }

    public function trace($msg)
    {
        $this->log($msg, 'TRACE');
    }

    public function info($msg)
    {
        $this->log($msg, 'INFO');
    }

    public function warning($msg)
    {
        $this->log($msg, 'WARNING');
    }

    public function error($msg)
    {
        $this->log($msg, 'ERROR');
    }

    public function callWithCatchException(callable $callback, array $args = [], $tries = 1)
    {
        $try = 0;
        do {
            $try++;
            try {
                return call_user_func_array($callback, $args);
            } catch (\Exception $e) {
                $this->logException($e);
            }
        } while ($try < $tries);
        return false;
    }
}