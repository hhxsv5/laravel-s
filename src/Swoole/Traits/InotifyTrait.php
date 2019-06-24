<?php

namespace Hhxsv5\LaravelS\Swoole\Traits;

use Hhxsv5\LaravelS\Console\Portal;
use Hhxsv5\LaravelS\Swoole\Inotify;
use Swoole\Http\Server;
use Swoole\Process;

trait InotifyTrait
{
    public function addInotifyProcess(Server $swoole, array $config, array $laravelConf)
    {
        if (empty($config['enable'])) {
            return;
        }

        if (!extension_loaded('inotify')) {
            $this->warning('Require extension inotify');
            return;
        }

        $fileTypes = isset($config['file_types']) ? (array)$config['file_types'] : [];
        if (empty($fileTypes)) {
            $this->warning('No file types to watch by inotify');
            return;
        }

        $autoReload = function () use ($config, $laravelConf) {
            $log = !empty($config['log']);
            $this->setProcessTitle(sprintf('%s laravels: inotify process', $config['process_prefix']));
            $inotify = new Inotify($config['watch_path'], IN_CREATE | IN_DELETE | IN_MODIFY | IN_MOVE,
                function ($event) use ($log, $laravelConf) {
                    // $swoole->reload();
                    $reloadCmd = trim(sprintf('%s -c "%s" %s/bin/laravels reload', PHP_BINARY, php_ini_loaded_file(), $laravelConf['root_path']));
                    Portal::runCommand($reloadCmd);
                    if ($log) {
                        $action = 'file:';
                        switch ($event['mask']) {
                            case IN_CREATE:
                                $action = 'create';
                                break;
                            case IN_DELETE:
                                $action = 'delete';
                                break;
                            case IN_MODIFY:
                                $action = 'modify';
                                break;
                            case IN_MOVE:
                                $action = 'move';
                                break;
                        }
                        $this->info(sprintf('reloaded by inotify, reason: %s %s', $action, $event['name']));
                    }
                });
            $inotify->addFileTypes($config['file_types']);
            if (empty($config['excluded_dirs'])) {
                $config['excluded_dirs'] = [];
            }
            $inotify->addExcludedDirs($config['excluded_dirs']);
            $inotify->watch();
            if ($log) {
                $this->info(sprintf('[Inotify] watched files: %d; file types: %s; excluded directories: %s',
                        $inotify->getWatchedFileCount(),
                        implode(',', $config['file_types']),
                        implode(',', $config['excluded_dirs'])
                    )
                );
            }
            $inotify->start();
        };

        $inotifyProcess = new Process($autoReload, false, false);
        if ($swoole->addProcess($inotifyProcess)) {
            return $inotifyProcess;
        }
    }
}