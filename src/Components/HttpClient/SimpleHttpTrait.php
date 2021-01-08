<?php

namespace Hhxsv5\LaravelS\Components\HttpClient;

use Hhxsv5\LaravelS\Swoole\Coroutine\Context;
use Swoole\Coroutine\Http\Client as CoroutineClient;

trait SimpleHttpTrait
{
    protected $curlOptions = [
        //bool
        CURLOPT_HEADER         => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_RETURNTRANSFER => true,

        //int
        CURLOPT_MAXREDIRS      => 3,
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_CONNECTTIMEOUT => 3,
    ];

    /**
     * Sends a GET request and returns a array response.
     * @param string $url
     * @param array $options
     * @return array
     */
    public function httpGet($url, array $options)
    {
        if (Context::inCoroutine()) {
            $parts = parse_url($url);
            $path = isset($parts['path']) ? $parts['path'] : '/';
            if (isset($parts['query'])) {
                $path .= '?' . $parts['query'];
            }
            if (isset($parts['fragment'])) {
                $path .= '#' . $parts['fragment'];
            }
            $client = new CoroutineClient($parts['host'], isset($parts['port']) ? $parts['port'] : 80, isset($parts['scheme']) && $parts['scheme'] === 'https');
            if (isset($options['timeout'])) {
                $client->set([
                    'timeout' => $options['timeout'],
                ]);
            }
            $client->get($path);
            $client->close();
            if ($client->errCode === 110) {
                return ['statusCode' => 0, 'headers' => [], 'body' => ''];
            }
            if ($client->errCode !== 0) {
                $msg = sprintf('Failed to send Http request(%s), errcode=%d, errmsg=%s', $url, $client->errCode, $client->errMsg);
                throw new \RuntimeException($msg, $client->errCode);
            }
            return ['statusCode' => $client->statusCode, 'headers' => $client->headers, 'body' => $client->body];
        }

        $handle = curl_init();
        $finalOptions = [
                CURLOPT_URL     => $url,
                CURLOPT_HTTPGET => true,
            ] + $this->curlOptions;
        if (isset($options['timeout'])) {
            $finalOptions[CURLOPT_TIMEOUT] = $options['timeout'];
        }
        curl_setopt_array($handle, $finalOptions);
        $responseStr = curl_exec($handle);
        $errno = curl_errno($handle);
        $errmsg = curl_error($handle);
        // Fix: curl_errno() always return 0 when fail
        if ($errno !== 0 || $errmsg !== '') {
            curl_close($handle);
            $msg = sprintf('Failed to send Http request(%s), errcode=%d, errmsg=%s', $url, $errno, $errmsg);
            throw new \RuntimeException($msg, $errno);
        }

        $headerSize = curl_getinfo($handle, CURLINFO_HEADER_SIZE);
        $statusCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        $header = substr($responseStr, 0, $headerSize);
        $body = substr($responseStr, $headerSize);
        $lines = explode("\n", $header);
        array_shift($lines); // Remove status

        $headers = [];
        foreach ($lines as $part) {
            $middle = explode(':', $part);
            $key = trim($middle[0]);
            if ($key === '') {
                continue;
            }
            if (isset($headers[$key])) {
                $headers[$key] = (array)$headers[$key];
                $headers[$key][] = isset($middle[1]) ? trim($middle[1]) : '';
            } else {
                $headers[$key] = isset($middle[1]) ? trim($middle[1]) : '';
            }
        }
        return ['statusCode' => $statusCode, 'headers' => $headers, 'body' => $body];
    }
}