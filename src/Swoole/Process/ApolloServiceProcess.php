<?php

namespace Hhxsv5\LaravelS\Swoole\Process;

use Hhxsv5\LaravelS\Components\Apollo\Apollo;
use Swoole\Http\Server;
use Swoole\Process;

class ApolloServiceProcess implements CustomProcessInterface
{
    /**@var Apollo $apollo */
    protected static $apollo;

    public static function getDefinition()
    {
        return [
            'apollo-service' => [
                'class'    => self::class,
                'redirect' => false,
                'pipe'     => 2, // SOCK_DGRAM
            ],
        ];
    }

    public static function callback(Server $swoole, Process $process)
    {
        self::$apollo = Apollo::createFromEnv();
        self::$apollo->startWatchNotification(function (array $notifications) use ($swoole) {
            try {
                self::$apollo->pullAllAndSave(base_path('.env'), false);
                $swoole->reload();
            } catch (\Exception $e) {
                sleep(3);
            }
        });
    }

    public static function onReload(Server $swoole, Process $process)
    {
        // Stop the process...
        self::$apollo->stopWatchNotification();
    }
}