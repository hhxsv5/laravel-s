<?php

namespace Hhxsv5\LaravelS\Illuminate;

use Hhxsv5\LaravelS\Swoole\Process\CustomProcessInterface;
use Illuminate\Support\Carbon;
use Swoole\Http\Server;
use Swoole\Process;
use Illuminate\Support\Arr;

/**
 * Monitor which uri is handling by Laravel-S
 */
class LaravelSMonitorProcess implements CustomProcessInterface
{
    private static $quit = false;

    /**
     * @inheritDoc
     */
    public static function getName()
    {
        return "laravel-s status monitor";
    }

    /**
     * @inheritDoc
     */
    public static function callback(Server $swoole, Process $process)
    {
        if (!extension_loaded('sockets')) {
            echo "ext-sockets is disabled, laravel-s monitor process will not working." . PHP_EOL;
            return;
        }

        $socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
        $file = static::sockFile();
        if (file_exists($file)) {
            @unlink($file);
        }
        socket_bind($socket, $file);
        socket_listen($socket);

        chmod($file, 0777);
        while (true) {
            $sock = socket_accept($socket);
            socket_write($sock, static::getSwooleStatus());
            socket_close($sock);
        }
    }

    /**
     * Get current status of Laravel-S worker
     *
     * @return string
     */
    public static function getSwooleStatus()
    {
        $data = app('swoole')->stats();
        $workerNum = config('laravels.swoole.worker_num');

        for ($i = 0; $i < $workerNum; $i++) {
            $value = Arr::get(app('swoole')->statusTable->get('worker:' . $i), 'value', '');

            if ($value) {
                $value = json_decode($value, true);
                $value['started_at'] = Carbon::createFromTimestamp(ceil($value['start_time'] / 1000))->toDateTimeString();
                $value['time'] = ceil(microtime(true) * 1000) - $value['start_time'];
                $value['time'] = static::formatMillisecond($value['time']) . " (Time elapsed)";

                $value = "{$value['method']} {$value['request_url']} {$value['time']} (started_at: {$value['started_at']})";
            }

            $data['start_times']['worker_' . $i] = $value;
        }

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Convert millisecond to human-readable format
     *
     * @param int $millisecond
     * @return string
     */
    private static function formatMillisecond($millisecond)
    {
        if ($millisecond < 1000) {
            // < 1s
            $result = $millisecond . "ms";
        } elseif ($millisecond < 1000 * 60) {
            // 1s < time < 1min
            $result = round($millisecond / 1000, 2) . "s";
        } else {
            // 1min < time
            $result = round($millisecond / 1000 * 60, 2) . "min";
        }

        return $result;
    }

    /**
     * Sock file to recording current Laravel-S worker status
     *
     * @return string
     */
    public static function sockFile()
    {
        return storage_path("swoole-status.sock");
    }

    /**
     * @inheritDoc
     */
    public static function onReload(Server $swoole, Process $process)
    {
        static::$quit = true;
    }
}
