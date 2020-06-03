<?php

namespace Hhxsv5\LaravelS\Components\Apollo;

use Hhxsv5\LaravelS\Components\HttpClient\SimpleHttpTrait;
use Hhxsv5\LaravelS\Swoole\Coroutine\Context;
use Swoole\Coroutine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

class Client
{
    use SimpleHttpTrait;

    protected $server;
    protected $appId;
    protected $cluster      = 'default';
    protected $namespaces   = ['application'];
    protected $clientIp;
    protected $pullTimeout  = 5;
    protected $backupOldEnv = false;

    protected $releaseKeys   = [];
    protected $notifications = [];

    protected $watching = true;

    public function __construct(array $settings)
    {
        $this->server = $settings['server'];
        $this->appId = $settings['app_id'];
        if (isset($settings['cluster'])) {
            $this->cluster = $settings['cluster'];
        }
        if (isset($settings['namespaces'])) {
            $this->namespaces = $settings['namespaces'];
        }
        if (isset($settings['client_ip'])) {
            $this->clientIp = $settings['client_ip'];
        } else {
            $this->clientIp = current(swoole_get_local_ip()) ?: null;
        }
        if (isset($settings['pull_timeout'])) {
            $this->pullTimeout = (int)$settings['pull_timeout'];
        }
        if (isset($settings['backup_old_env'])) {
            $this->backupOldEnv = (bool)$settings['backup_old_env'];
        }
    }

    public static function putCommandOptionsToEnv(array $options)
    {
        $envs = [
            'ENABLE_APOLLO'         => !empty($options['enable-apollo']),
            'APOLLO_SERVER'         => $options['apollo-server'],
            'APOLLO_APP_ID'         => $options['apollo-app-id'],
            'APOLLO_CLUSTER'        => $options['apollo-cluster'],
            'APOLLO_NAMESPACES'     => implode(',', $options['apollo-namespaces']),
            'APOLLO_CLIENT_IP'      => $options['apollo-client-ip'],
            'APOLLO_PULL_TIMEOUT'   => $options['apollo-pull-timeout'],
            'APOLLO_BACKUP_OLD_ENV' => $options['apollo-backup-old-env'],
        ];
        foreach ($envs as $key => $value) {
            putenv("{$key}={$value}");
        }
    }

    public static function createFromEnv()
    {
        if (!getenv('APOLLO_SERVER') || !getenv('APOLLO_APP_ID')) {
            throw new \InvalidArgumentException('Missing environment variable APOLLO_SERVER or APOLLO_APP_ID');
        }
        $settings = [
            'server'         => getenv('APOLLO_SERVER'),
            'app_id'         => getenv('APOLLO_APP_ID'),
            'cluster'        => ($cluster = (string)getenv('APOLLO_CLUSTER')) !== '' ? $cluster : null,
            'namespaces'     => ($namespaces = (string)getenv('APOLLO_NAMESPACES')) !== '' ? explode(',', $namespaces) : null,
            'client_ip'      => ($clientIp = (string)getenv('APOLLO_CLIENT_IP')) !== '' ? $clientIp : null,
            'pull_timeout'   => ($pullTimeout = (int)getenv('APOLLO_PULL_TIMEOUT')) > 0 ? $pullTimeout : null,
            'backup_old_env' => ($backupOldEnv = (bool)getenv('APOLLO_BACKUP_OLD_ENV')) ? $backupOldEnv : null,
        ];
        return new static($settings);
    }

    public static function createFromCommandOptions(array $options)
    {
        if (!isset($options['apollo-server'], $options['apollo-app-id'])) {
            throw new \InvalidArgumentException('Missing command option apollo-server or apollo-app-id');
        }
        $settings = [
            'server'         => $options['apollo-server'],
            'app_id'         => $options['apollo-app-id'],
            'cluster'        => isset($options['apollo-cluster']) && $options['apollo-cluster'] !== '' ? $options['apollo-cluster'] : null,
            'namespaces'     => !empty($options['apollo-namespaces']) ? $options['apollo-namespaces'] : null,
            'client_ip'      => isset($options['apollo-client-ip']) && $options['apollo-client-ip'] !== '' ? $options['apollo-client-ip'] : null,
            'pull_timeout'   => isset($options['apollo-pull-timeout']) ? (int)$options['apollo-pull-timeout'] : null,
            'backup_old_env' => isset($options['apollo-backup-old-env']) ? (bool)$options['apollo-backup-old-env'] : null,
        ];
        return new static($settings);
    }

    public static function attachCommandOptions(Command $command)
    {
        $command->addOption('enable-apollo', null, InputOption::VALUE_NONE, 'Whether to enable Apollo component');
        $command->addOption('apollo-server', null, InputOption::VALUE_OPTIONAL, 'Apollo server URL');
        $command->addOption('apollo-app-id', null, InputOption::VALUE_OPTIONAL, 'Apollo APP ID');
        $command->addOption('apollo-namespaces', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'The namespace to which the APP belongs');
        $command->addOption('apollo-cluster', null, InputOption::VALUE_OPTIONAL, 'The cluster to which the APP belongs');
        $command->addOption('apollo-client-ip', null, InputOption::VALUE_OPTIONAL, 'IP of current instance');
        $command->addOption('apollo-pull-timeout', null, InputOption::VALUE_OPTIONAL, 'Timeout time(seconds) when pulling configuration');
        $command->addOption('apollo-backup-old-env', null, InputOption::VALUE_NONE, 'Whether to backup the old configuration file when updating the configuration .env file');
    }

    public function pullBatch(array $namespaces, $withReleaseKey = false, array $options = [])
    {
        $configs = [];
        $uri = sprintf('%s/configs/%s/%s/', $this->server, $this->appId, $this->cluster);
        foreach ($namespaces as $namespace) {
            $url = $uri . $namespace . '?' . http_build_query([
                    'releaseKey' => $withReleaseKey && isset($this->releaseKeys[$namespace]) ? $this->releaseKeys[$namespace] : null,
                    'ip'         => $this->clientIp,
                ]);
            $timeout = isset($options['timeout']) ? $options['timeout'] : $this->pullTimeout;
            $response = $this->httpGet($url, compact('timeout'));
            if ($response['statusCode'] === 200) {
                $json = json_decode($response['body'], true);
                if (is_array($json)) {
                    $configs[$namespace] = $json;
                    $this->releaseKeys[$namespace] = $configs[$namespace]['releaseKey'];
                }
            } elseif ($response['statusCode'] === 304) {
                // ignore 304
            }

        }
        return $configs;
    }

    public function pullAll($withReleaseKey = false, array $options = [])
    {
        return $this->pullBatch($this->namespaces, $withReleaseKey, $options);
    }

    public function pullAllAndSave($filepath, array $options = [])
    {
        $all = $this->pullAll(false, $options);
        if (count($all) !== count($this->namespaces)) {
            $lackNamespaces = array_diff($this->namespaces, array_keys($all));
            throw new \RuntimeException('Missing Apollo configurations for namespaces ' . implode(',', $lackNamespaces));
        }
        $configs = [];
        foreach ($all as $namespace => $config) {
            $configs[] = '# Namespace: ' . $config['namespaceName'];
            ksort($config['configurations']);
            foreach ($config['configurations'] as $key => $value) {
                $configs[] = sprintf('%s=%s', $key, $value);
            }
        }
        if (empty($configs)) {
            throw new \RuntimeException('Empty Apollo configuration list');
        }
        if ($this->backupOldEnv && file_exists($filepath)) {
            rename($filepath, $filepath . '.' . date('YmdHis'));
        }
        $fileContent = implode(PHP_EOL, $configs);
        if (Context::inCoroutine()) {
            Coroutine::writeFile($filepath, $fileContent);
        } else {
            file_put_contents($filepath, $fileContent);
        }
        return $configs;
    }

    public function startWatchNotification(callable $callback, array $options = [])
    {
        if (!isset($options['timeout']) || $options['timeout'] < 60) {
            $options['timeout'] = 70;
        }
        $this->watching = true;
        $this->notifications = [];
        foreach ($this->namespaces as $namespace) {
            $this->notifications[$namespace] = ['namespaceName' => $namespace, 'notificationId' => -1];
        }
        while ($this->watching) {
            $url = sprintf('%s/notifications/v2?%s',
                $this->server,
                http_build_query([
                    'appId'         => $this->appId,
                    'cluster'       => $this->cluster,
                    'notifications' => json_encode(array_values($this->notifications)),
                ])
            );
            $response = $this->httpGet($url, $options);

            if ($response['statusCode'] === 200) {
                $notifications = json_decode($response['body'], true);
                if (empty($notifications)) {
                    continue;
                }
                if (!empty($this->notifications) && current($this->notifications)['notificationId'] !== -1) { // Ignore the first pull
                    $callback($notifications);
                }
                array_walk($notifications, function (&$notification) {
                    unset($notification['messages']);
                });
                $this->notifications = array_merge($this->notifications, array_column($notifications, null, 'namespaceName'));
            } elseif ($response['statusCode'] === 304) {
                // ignore 304
            }
        }
    }

    public function stopWatchNotification()
    {
        $this->watching = false;
    }
}
