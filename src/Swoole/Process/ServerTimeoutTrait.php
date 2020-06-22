<?php


namespace Hhxsv5\LaravelS\Swoole\Process;


trait ServerTimeoutTrait
{
    public function resetAlarm()
    {
        if ($this->getServerTimeout() != -1) {
            \Swoole\Process::alarm(-1);
        }
    }

    public function handleServerTimeout()
    {
        if ($this->getServerTimeout() != -1) {
            \Swoole\Process::alarm($this->conf['swoole']['server_timeout'] * 1000 * 1000);
        }
    }

    public function registerSignal()
    {
        if ($this->getServerTimeout() != -1) {
            pcntl_signal(SIGALRM, function () {
                \Swoole\Process::alarm(-1);
                throw new \Exception();
            });
        }
    }
}
