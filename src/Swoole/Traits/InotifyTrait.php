<?php

namespace Hhxsv5\LaravelS\Swoole\Traits;

use Hhxsv5\LaravelS\Swoole\Inotify;

trait InotifyTrait
{
    use ProcessTitleTrait;
    use LogTrait;

    public function addInotifyProcess(\swoole_server $swoole, array $config)
    {
        if (empty($config['enable'])) {
            return;
        }

        if (!extension_loaded('inotify')) {
            $this->log('Require extension inotify', 'WARN');
            return;
        }

        $fileTypes = isset($config['file_types']) ? (array)$config['file_types'] : [];
        if (empty($fileTypes)) {
            $this->log('No file types to watch by inotify', 'WARN');
            return;
        }

        $autoReload = function () use ($swoole, $config, $fileTypes) {
            $log = !empty($config['log']);
            $this->setProcessTitle(sprintf('%s laravels: inotify process', $config['process_prefix']));
            $inotify = new Inotify($config['watch_path'], IN_CREATE | IN_DELETE | IN_MODIFY | IN_MOVE,
                function ($event) use ($swoole, $log) {
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
        if ($swoole->addProcess($inotifyProcess)) {
            return $inotifyProcess;
        }
    }

}