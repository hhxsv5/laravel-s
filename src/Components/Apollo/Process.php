<?php

namespace Hhxsv5\LaravelS\Components\Apollo;

use Hhxsv5\LaravelS\Console\Portal;
use Hhxsv5\LaravelS\Swoole\Coroutine\Context;
use Hhxsv5\LaravelS\Swoole\Process\CustomProcessInterface;
use Swoole\Coroutine;
use Swoole\Http\Server;
use Swoole\Process as SwooleProcess;

class Process implements CustomProcessInterface
{
    /**@var Client $apollo */
    protected static $apollo;

    public static function getDefinition()
    {
        return [
            'apollo' => [
                'class'    => self::class,
                'redirect' => false,
                'pipe'     => 0,
                'enable'   => (bool)getenv('ENABLE_APOLLO'),
            ],
        ];
    }

    public static function callback(Server $swoole, SwooleProcess $process)
    {
        $filename = base_path('.env');
        if (isset($_ENV['_ENV'])) {
            $filename .= '.' . $_ENV['_ENV'];
        }

        self::$apollo = Client::createFromEnv();
        self::$apollo->startWatchNotification(function (array $notifications) use ($process, $filename) {
            $configs = self::$apollo->pullAllAndSave($filename);
            app('log')->info('[ApolloProcess] Pull all configurations', $configs);
            Portal::runLaravelSCommand(base_path(), 'reload');
            if (Context::inCoroutine()) {
                Coroutine::sleep(5);
            } else {
                sleep(5);
            }
        });
    }

    public static function onReload(Server $swoole, SwooleProcess $process)
    {
        // Stop the process...
        self::$apollo->stopWatchNotification();
    }
}