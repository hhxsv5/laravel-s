<?php

namespace Hhxsv5\LaravelS\Console;

use Hhxsv5\LaravelS\Illuminate\LogTrait;
use Hhxsv5\LaravelS\LaravelS;
use Swoole\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Portal extends Command
{
    use LogTrait;


    /**@var string */
    protected $basePath;

    /**@var InputInterface */
    protected $input;

    /**@var OutputInterface */
    protected $output;

    public function __construct($basePath)
    {
        parent::__construct('laravels');
        $this->basePath = $basePath;
    }

    protected function configure()
    {
        $this->setDescription('LaravelS console tool');
        $this->setHelp('LaravelS console tool');

        $this->addArgument('action', InputArgument::OPTIONAL, 'start|stop|restart|reload|info|help', 'help');
        $this->addOption('env', 'e', InputOption::VALUE_OPTIONAL, 'The environment the command should run under, this feature requires Laravel 5.2+');
        $this->addOption('daemonize', 'd', InputOption::VALUE_NONE, 'Whether run as a daemon for "start & restart"');
        $this->addOption('ignore', 'i', InputOption::VALUE_NONE, 'Whether ignore checking process pid for "start & restart"');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        LaravelS::setOutputStyle(new SymfonyStyle($this->input, $this->output));

        try {
            $action = $input->getArgument('action');
            switch ($action) {
                case 'start':
                    $this->start();
                    break;
                case 'stop':
                    $this->stop();
                    break;
                case 'restart':
                    $this->restart();
                    break;
                case 'reload':
                    $this->reload();
                    break;
                case 'info':
                    $this->showInfo();
                    break;
                default:
                    $help = <<<EOS

Usage: 
  [%s] ./bin/laravels [options] <action>

Arguments:
  action                start|stop|restart|reload|info|help

Options:
  -e, --env             The environment the command should run under, this feature requires Laravel 5.2+
  -d, --daemonize       Whether run as a daemon for "start & restart"
  -i, --ignore          Whether ignore checking process pid for "start & restart"
EOS;

                    $this->info(sprintf($help, PHP_BINARY));
                    break;
            }
        } catch (\Exception $e) {
            $error = sprintf(
                'Uncaught exception "%s"([%d]%s) at %s:%s, %s%s',
                get_class($e),
                $e->getCode(),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                PHP_EOL,
                $e->getTraceAsString()
            );
            $this->error($error);
        }
    }

    public function start()
    {
        if (!extension_loaded('swoole')) {
            $this->error('LaravelS requires swoole extension, try to `pecl install swoole` and `php --ri swoole`.');
            return 1;
        }

        // Initialize configuration config/laravels.json
        $options = $this->input->getOptions();
        unset($options['env']);
        $options = array_filter($options);
        $optionStr = '';
        foreach ($options as $key => $value) {
            $optionStr .= sprintf('--%s%s ', $key, is_bool($value) ? '' : ('=' . $value));
        }
        $this->runArtisanCommand(trim('laravels config ' . $optionStr));

        // Here we go...
        $config = $this->getConfig();

        if (!$config['server']['ignore_check_pid'] && file_exists($config['server']['swoole']['pid_file'])) {
            $pid = (int)file_get_contents($config['server']['swoole']['pid_file']);
            if ($pid > 0 && self::kill($pid, 0)) {
                $this->warning(sprintf('Swoole[PID=%d] is already running.', $pid));
                return 1;
            }
        }

        if ($config['server']['swoole']['daemonize']) {
            $this->trace('Swoole is running in daemon mode, see "ps -ef|grep laravels".');
        } else {
            $this->trace('Swoole is running, press Ctrl+C to quit.');
        }

        (new LaravelS($config['server'], $config['laravel']))->run();

        return 0;
    }

    public function stop()
    {
        $config = $this->getConfig();
        $pidFile = $config['server']['swoole']['pid_file'];
        if (!file_exists($pidFile)) {
            $this->warning('It seems that Swoole is not running.');
            return 0;
        }

        $pid = file_get_contents($pidFile);
        if (self::kill($pid, 0)) {
            if (self::kill($pid, SIGTERM)) {
                // Make sure that master process quit
                $time = 1;
                $waitTime = isset($config['server']['swoole']['max_wait_time']) ? $config['server']['swoole']['max_wait_time'] : 60;
                $this->info("The max time of waiting to forcibly stop is {$waitTime}s.");
                while (self::kill($pid, 0)) {
                    if ($time > $waitTime) {
                        $this->warning("Swoole [PID={$pid}] cannot be stopped gracefully in {$waitTime}s, will be stopped forced right now.");
                        return 1;
                    }
                    $this->info("Waiting Swoole[PID={$pid}] to stop. [{$time}]");
                    sleep(1);
                    $time++;
                }
                $basePath = dirname($pidFile);
                $deleteFiles = [
                    $pidFile,
                    $basePath . '/laravels-custom-processes.pid',
                    $basePath . '/laravels-timer-process.pid',
                ];
                foreach ($deleteFiles as $deleteFile) {
                    if (file_exists($deleteFile)) {
                        unlink($deleteFile);
                    }
                }
                $this->info("Swoole [PID={$pid}] is stopped.");
                return 0;
            } else {
                $this->error("Swoole [PID={$pid}] is stopped failed.");
                return 1;
            }
        } else {
            $this->warning("Swoole [PID={$pid}] does not exist, or permission denied.");
            return 0;
        }
    }

    public function restart()
    {
        $code = $this->stop();
        if ($code !== 0) {
            return $code;
        }
        return $this->start();
    }

    public function reload()
    {
        $config = $this->getConfig();
        $pidFile = $config['server']['swoole']['pid_file'];
        if (!file_exists($pidFile)) {
            $this->error('It seems that Swoole is not running.');
            return;
        }

        // Reload worker processes
        $pid = file_get_contents($pidFile);
        if (!$pid || !self::kill($pid, 0)) {
            $this->error("Swoole [PID={$pid}] does not exist, or permission denied.");
            return;
        }
        if (self::kill($pid, SIGUSR1)) {
            $this->info("Swoole [PID={$pid}] is reloaded.");
        } else {
            $this->error("Swoole [PID={$pid}] is reloaded failed.");
        }

        // Reload custom processes
        $pidFile = dirname($pidFile) . '/laravels-custom-processes.pid';
        if (file_exists($pidFile)) {
            $pids = (array)explode("\n", trim(file_get_contents($pidFile)));
            unlink($pidFile);
            foreach ($pids as $pid) {
                if (!$pid || !self::kill($pid, 0)) {
                    $this->error("Custom process[PID={$pid}] does not exist, or permission denied.");
                    continue;
                }

                if (self::kill($pid, SIGUSR1)) {
                    $this->info("Custom process[PID={$pid}] is reloaded.");
                } else {
                    $this->error("Custom process[PID={$pid}] is reloaded failed.");
                }
            }
        }

        // Reload timer process
        if (!empty($config['server']['timer']['enable'])) {
            $pidFile = dirname($pidFile) . '/laravels-timer-process.pid';
            $pid = file_get_contents($pidFile);
            if (!$pid || !self::kill($pid, 0)) {
                $this->error("Timer process[PID={$pid}] does not exist, or permission denied.");
                return;
            }

            if (self::kill($pid, SIGUSR1)) {
                $this->info("Timer process[PID={$pid}] is reloaded.");
            } else {
                $this->error("Timer process[PID={$pid}] is reloaded failed.");
            }
        }
    }

    public function showInfo()
    {
        $this->runArtisanCommand('laravels info');
    }

    public function artisanCmd($subCmd)
    {
        $phpCmd = sprintf('%s -c "%s"', PHP_BINARY, php_ini_loaded_file());
        $env = $this->input->getOption('env');
        $envs = $env ? "APP_ENV={$env}" : '';
        $artisanCmd = trim(sprintf('%s %s %s/artisan %s', $envs, $phpCmd, $this->basePath, $subCmd));
        return $artisanCmd;
    }

    public function runArtisanCommand($cmd)
    {
        $cmd = $this->artisanCmd($cmd);
        self::runCommand($cmd);
    }

    public function getConfig()
    {
        $json = file_get_contents($this->basePath . '/storage/laravels.json');
        return (array)json_decode($json, true);
    }

    public static function runCommand($cmd, $input = null)
    {
        $fp = popen($cmd, 'w');
        if ($fp === false) {
            return false;
        }
        if ($input !== null) {
            fwrite($fp, $input);
        }
        pclose($fp);
        return true;
    }

    public static function kill($pid, $sig)
    {
        try {
            return Process::kill((int)$pid, $sig);
        } catch (\Exception $e) {
            return false;
        }
    }
}
