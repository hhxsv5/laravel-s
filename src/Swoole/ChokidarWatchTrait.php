<?php

namespace Hhxsv5\LaravelS\Swoole;

use Hhxsv5\LaravelS\Console\Portal;
use Swoole\Http\Server;
use Swoole\Process;
use Symfony\Component\Process\ExecutableFinder;

trait ChokidarWatchTrait
{
    public function addChokidarWatchProcess(Server $swoole, array $config, array $laravelConf)
    {
        if (empty($config['enable'])) {
            return false;
        }

        $watchPathFiles = isset($config['watch_path_files']) ? (array)$config['watch_path_files'] : [];
        if (empty($watchPathFiles)) {
            $this->warning('No file to watch by chokidar');
            return false;
        }

        $callback = function (Process $worker) use ($config, $laravelConf, $watchPathFiles) {
            $log = !empty($config['log']);
            $watch_base_path = $config['watch_base_path'];
            $this->setProcessTitle(sprintf('%s laravels: chokidar process', $config['process_prefix']));
            $nodeExecutable = (new ExecutableFinder)->find('node');
            $nodeScriptPath = realpath(__DIR__.'/../../bin/file-watcher.cjs');
            $this->info(sprintf('$nodeScriptPath: %s', $nodeScriptPath));

            $watchOptions = isset($config['watch_options']) ? (array)$config['watch_options'] : ['ignoreInitial' => true];
            $worker->exec($nodeExecutable, [$nodeScriptPath,
                json_encode(collect($watchPathFiles)->map(fn ($path) => $watch_base_path.'/'.$path)),
                json_encode($watchOptions),
            ]);

            // 获取 Node.js 脚本的输出
            tap($worker->read(), function ($output) use ($log, $laravelConf) {
                Portal::runLaravelSCommand($laravelConf['root_path'], 'reload');
                if ($log) {
                    $this->info(sprintf('reloaded by chokidar, reason: %s', $output));
                }
            });
        };

        $process = new Process($callback, false, 0);
        $swoole->addProcess($process);
        return $process;
    }
}
