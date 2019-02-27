<?php

namespace Hhxsv5\LaravelS\Swoole\Process;

interface CustomProcessInterface
{
    /**
     * The name of process
     * @return string
     */
    public static function getName();

    /**
     * The run callback of process
     * @param \swoole_server $swoole
     * @param \swoole_process $process
     * @return void
     */
    public static function callback(\swoole_server $swoole, \swoole_process $process);

    /**
     * Whether redirect stdin/stdout
     * @return bool
     */
    public static function isRedirectStdinStdout();

    /**
     * The type of pipeline
     * 0: no pipeline
     * 1: \SOCK_STREAM
     * 2: \SOCK_DGRAM
     * @return int
     */
    public static function getPipeType();

    /**
     * Trigger this method on receiving the signal SIGUSR1
     * @param \swoole_process $process
     * @return mixed
     */
    public static function onReload(\swoole_process $process);
}