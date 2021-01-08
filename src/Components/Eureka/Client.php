<?php

namespace Hhxsv5\LaravelS\Components\Eureka;

class Client
{
    protected $host;

    protected $port;

    protected $context;

    public function __construct($host, $port, $context = 'eureka/v2 ')
    {
        $this->host = $host;
        $this->port = $port;
        $this->context = $context;
    }

    protected function getEurekaUri()
    {
        return $this->host . ':' . $this->port . '/' . $this->context;
    }

    /**
     * Register app in eureka.
     *
     * @param string $appId
     * @param array $data
     * @return
     */
    public function register($appId, array $data)
    {
        return $this->client->request('POST', $this->getEurekaUri() . '/apps/' . $appId, [
            'json' => [
                'instance' => $data,
            ],
        ]);
    }

    /**
     * De-register app from eureka.
     *
     * @param string $appId
     * @param string $instanceId
     *
     * @return ResponseInterface
     * @throws GuzzleException
     *
     */
    public function deRegister($appId, $instanceId)
    {
        return $this->client->request('DELETE', $this->getEurekaUri() . '/apps/' . $appId . '/' . $instanceId);
    }

    /**
     * Send app heartbeat.
     *
     * @param string $appId
     * @param string $instanceId
     *
     * @return ResponseInterface
     * @throws GuzzleException
     *
     */
    public function heartBeat($appId, $instanceId)
    {
        return $this->client->request('PUT', $this->getEurekaUri() . '/apps/' . $appId . '/' . $instanceId);
    }

    /**
     * Get all registered applications.
     *
     * @return array
     * @throws GuzzleException
     *
     */
    public function getAllApps()
    {
        $response = $this->client->request('GET', $this->getEurekaUri() . '/apps', [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        return \GuzzleHttp\json_decode($response->getBody(), true);
    }

    /**
     * Get application.
     *
     * @param string $appId
     *
     * @return array
     * @throws GuzzleException
     *
     */
    public function getApp($appId)
    {
        $response = $this->client->request('GET', $this->getEurekaUri() . '/apps/' . $appId, [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        return \GuzzleHttp\json_decode($response->getBody(), true);
    }

    /**
     * Get application Instance.
     *
     * @param string $appId
     * @param string $instanceId
     *
     * @return array
     * @throws GuzzleException
     *
     */
    public function getAppInstance($appId, $instanceId)
    {
        $response = $this->client->request('GET', $this->getEurekaUri() . '/apps/' . $appId . '/' . $instanceId, [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        return \GuzzleHttp\json_decode($response->getBody(), true);
    }

    /**
     * Get Instance.
     *
     * @param string $instanceId
     *
     * @return array
     * @throws GuzzleException
     *
     */
    public function getInstance($instanceId)
    {
        $response = $this->client->request('GET', $this->getEurekaUri() . '/instances/' . $instanceId, [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Take Instance out of the service.
     *
     * @param string $appId
     * @param string $instanceId
     *
     *
     */
    public function takeInstanceOut($appId, $instanceId)
    {
        return $this->client->request('PUT', $this->getEurekaUri() . '/apps/' . $appId . '/' . $instanceId . '/status', [
            'query' => [
                'value' => 'OUT_OF_SERVICE',
            ],
        ]);
    }

    /**
     * Put Instance back into the service.
     *
     * @param string $appId
     * @param string $instanceId
     *
     * @return ResponseInterface
     * @throws GuzzleException
     *
     */
    public function putInstanceBack($appId, $instanceId)
    {
        return $this->client->request('PUT', $this->getEurekaUri() . '/apps/' . $appId . '/' . $instanceId . '/status', [
            'query' => [
                'value' => 'UP',
            ],
        ]);
    }

    /**
     * Update app Instance metadata.
     *
     * @param string $appId
     * @param string $instanceId
     * @param array $metadata
     *
     * @return ResponseInterface
     * @throws GuzzleException
     *
     */
    public function updateAppInstanceMetadata($appId, $instanceId, array $metadata)
    {
        return $this->client->request('PUT', $this->getEurekaUri() . '/apps/' . $appId . '/' . $instanceId . '/metadata', [
            'query' => $metadata,
        ]);
    }

    /**
     * Get all instances by a vip address.
     *
     * @param string $vipAddress
     *
     */
    public function getInstancesByVipAddress($vipAddress)
    {
        $response = $this->client->request('GET', $this->getEurekaUri() . '/vips/' . $vipAddress, [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * Get all instances by a secure vip address.
     *
     * @param string $secureVipAddress
     *
     * @return array
     */
    public function getInstancesBySecureVipAddress($secureVipAddress)
    {
        $response = $this->client->request('GET', $this->getEurekaUri() . '/svips/' . $secureVipAddress, [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    public function up(string $appId, string $instanceId)
    {
        return $this->client->request('PUT', $this->getEurekaUri() . '/apps/' . $appId . '/' . $instanceId . '/status', [
            'query' => [
                'value' => 'UP',
            ],
        ]);
    }

    public function down($appId, $instanceId)
    {
        return $this->client->request('PUT', $this->getEurekaUri() . '/apps/' . $appId . '/' . $instanceId . '/status', [
            'query' => [
                'value' => 'DOWN',
            ],
        ]);
    }

    /**
     * Get the alive instances
     * @param string $appId
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUpInstances($appId)
    {
        $apps = $this->getApp($appId);
        return array_filter($apps['application']['instance'], function ($instance) {
            return $instance['status'] === 'UP';
        });
    }

    /**
     * Get the url of instance.
     * @param array $instance
     * @return string
     */
    public function getInstanceUrl(array $instance)
    {
        if ($instance['securePort']['@enabled'] === 'true') {
            $url = sprintf('%s://%s:%d', 'https', $instance['ipAddr'], $instance['securePort']['$']);
        } elseif ($instance['port']['@enabled'] === 'true') {
            $url = sprintf('%s://%s:%d', 'http', $instance['ipAddr'], $instance['port']['$']);
        } else {
            $parts = parse_url($instance['homePageUrl']);
            if (!isset($parts['host'])) {
                throw new \RuntimeException('Invalid homePageUrl: ' . $instance['homePageUrl']);
            }
            $url = sprintf('%s://%s:%d', isset($parts['scheme']) ? $parts['scheme'] : 'http', $parts['host'], isset($parts['port']) ? $parts['port'] : 80);
        }
        return $url;
    }
}
