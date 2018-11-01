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

        $autoReload = function () use ($swoole, $config) {
            $log = !empty($config['log']);
            $this->setProcessTitle(sprintf('%s laravels: inotify process', $config['process_prefix']));
            $inotify = new Inotify($config['watch_path'], IN_CREATE | IN_DELETE | IN_MODIFY | IN_MOVE,
                function ($event) use ($swoole, $log) {
                    $swoole->reload();
                    if ($log) {
                        $action = 'file:';
                        switch ($event['mask']) {
                            case IN_CREATE:
                                $action = 'create';
                                break;
                            case IN_DELETE:
                                $action = 'delete';
                                break;
                            case IN_MODIFY :
                                $action = 'modify';
                                break;
                            case IN_MOVE:
                                $action = 'move';
                                break;
                        }
                        $this->log(sprintf('reloaded by inotify, reason: %s %s', $action, $event['name']));
                    }
                });
            $inotify->addFileTypes($config['file_types']);
            if (empty($config['excluded_dirs'])) {
                $config['excluded_dirs'] = [];
            }
            $inotify->addExcludedDirs($config['excluded_dirs']);
            $inotify->watch();
            if ($log) {
                $this->log(sprintf('[Inotify] watched files: %d; file types: %s; excluded directories: %s',
                        $inotify->getWatchedFileCount(),
                        implode(',', $config['file_types']),
                        implode(',', $config['excluded_dirs'])
                    )
                );
            }
            $inotify->start();
        };

        $inotifyProcess = new \swoole_process($autoReload, false, false);
        if ($swoole->addProcess($inotifyProcess)) {
            return $inotifyProcess;
        }
    }

}