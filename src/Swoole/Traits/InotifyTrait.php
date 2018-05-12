<?php

namespace Hhxsv5\LaravelS\Swoole\Traits;

use Hhxsv5\LaravelS\Inotify;

trait InotifyTrait
{
    use ProcessTitleTrait;

    public function addInotifyProcess(\swoole_server $swoole, array $config)
    {
        if (empty($config['enable'])) {
            return;
        }

        if (!extension_loaded('inotify')) {
            $this->log('require extension inotify', 'WARN');
            return;
        }

        $autoReload = function () use ($swoole, $config) {
            $log = !empty($config['log']);
            $fileTypes = isset($config['file_types']) ? (array)$config['file_types'] : [];
            $this->setProcessTitle(sprintf('%s laravels: inotify process', $config['process_prefix']));
            $inotify = new Inotify($config['root_path'], IN_CREATE | IN_MODIFY | IN_DELETE, function ($event) use ($swoole, $log) {
                $swoole->reload();
                if ($log) {
                    $this->log(sprintf('reloaded by inotify, file: %s', $event['name']));
                }
            });
            $inotify->addFileTypes($fileTypes);
            $inotify->watch();
            if ($log) {
                $this->log(sprintf('count of watched files by inotify: %d', $inotify->getWatchedFileCount()));
            }
            $inotify->start();
        };

        $inotifyProcess = new \swoole_process($autoReload, false, false);
        $swoole->addProcess($inotifyProcess);
    }

}