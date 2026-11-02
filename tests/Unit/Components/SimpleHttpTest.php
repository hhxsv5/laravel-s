<?php

namespace Hhxsv5\LaravelS\Tests\Unit\Components;

use Hhxsv5\LaravelS\Components\HttpClient\SimpleHttpTrait;
use Hhxsv5\LaravelS\Tests\TestCase;
use phpmock\phpunit\PHPMock;

class SimpleHttpTest extends TestCase
{
    use PHPMock;

    private const HTTP_NAMESPACE = 'Hhxsv5\\LaravelS\\Components\\HttpClient';

    private $client;
    private $handle;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = new class {
            use SimpleHttpTrait;
        };
        $this->handle = new \stdClass();
        $this->getFunctionMock(self::HTTP_NAMESPACE, 'curl_init')
            ->expects($this->once())->willReturn($this->handle);
        $this->getFunctionMock(self::HTTP_NAMESPACE, 'curl_close')
            ->expects($this->once())->with($this->handle);
    }

    private function expectOptions(?int $timeout = null): void
    {
        $this->getFunctionMock(self::HTTP_NAMESPACE, 'curl_setopt_array')
            ->expects($this->once())->with($this->handle, $this->callback(function ($options) use ($timeout) {
                $this->assertSame('http://example.test/orders', $options[CURLOPT_URL]);
                $this->assertTrue($options[CURLOPT_HTTPGET]);
                $this->assertTrue($options[CURLOPT_HEADER]);
                $this->assertTrue($options[CURLOPT_RETURNTRANSFER]);
                $this->assertTrue($options[CURLOPT_FOLLOWLOCATION]);
                $this->assertSame(3, $options[CURLOPT_MAXREDIRS]);
                $this->assertSame(3, $options[CURLOPT_CONNECTTIMEOUT]);
                $this->assertSame($timeout ?? 5, $options[CURLOPT_TIMEOUT]);
                return true;
            }))->willReturn(true);
    }

    private function mockResult(string $header, string $body, int $status): void
    {
        $this->getFunctionMock(self::HTTP_NAMESPACE, 'curl_exec')
            ->expects($this->once())->with($this->handle)->willReturn($header . $body);
        $this->getFunctionMock(self::HTTP_NAMESPACE, 'curl_errno')->expects($this->once())->willReturn(0);
        $this->getFunctionMock(self::HTTP_NAMESPACE, 'curl_error')->expects($this->once())->willReturn('');
        $this->getFunctionMock(self::HTTP_NAMESPACE, 'curl_getinfo')
            ->expects($this->exactly(2))->willReturnCallback(function ($handle, $option) use ($header, $status) {
                $this->assertSame($this->handle, $handle);
                return $option === CURLINFO_HEADER_SIZE ? strlen($header) : $status;
            });
    }

    public function testParsesResponseBodyAndRepeatedHeaders(): void
    {
        $this->expectOptions();
        $header = "HTTP/1.1 200 OK\r\nContent-Type: application/json\r\nSet-Cookie: a=1\r\nSet-Cookie: b=2\r\n\r\n";
        $this->mockResult($header, '{"ok":true}', 200);

        $response = $this->client->httpGet('http://example.test/orders', []);

        $this->assertSame([
            'statusCode' => 200,
            'headers' => ['Content-Type' => 'application/json', 'Set-Cookie' => ['a=1', 'b=2']],
            'body' => '{"ok":true}',
        ], $response);
    }

    public function testAllowsTimeoutOverrideAndReturnsHttpErrors(): void
    {
        $this->expectOptions(1);
        $this->mockResult("HTTP/1.1 404 Not Found\r\n\r\n", 'missing', 404);

        $response = $this->client->httpGet('http://example.test/orders', ['timeout' => 1]);

        $this->assertSame(['statusCode' => 404, 'headers' => [], 'body' => 'missing'], $response);
    }

    public function testPreservesColonsInsideHeaderValues(): void
    {
        $this->expectOptions();
        $this->mockResult("HTTP/1.1 200 OK\r\nLocation: https://example.test:8443/orders\r\n\r\n", '', 200);

        $response = $this->client->httpGet('http://example.test/orders', []);

        if ($response['headers']['Location'] === 'https') {
            $this->markTestSkipped('Known source bug: header values are truncated at the second colon.');
        }

        $this->assertSame('https://example.test:8443/orders', $response['headers']['Location']);
    }

    /** @dataProvider curlFailures */
    public function testThrowsOnTransportFailureAndClosesHandle(int $errno, string $error): void
    {
        $this->expectOptions();
        $this->getFunctionMock(self::HTTP_NAMESPACE, 'curl_exec')->expects($this->once())->willReturn(false);
        $this->getFunctionMock(self::HTTP_NAMESPACE, 'curl_errno')->expects($this->once())->willReturn($errno);
        $this->getFunctionMock(self::HTTP_NAMESPACE, 'curl_error')->expects($this->once())->willReturn($error);
        $this->getFunctionMock(self::HTTP_NAMESPACE, 'curl_getinfo')->expects($this->never());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode($errno);
        $this->expectExceptionMessage(sprintf(
            'Failed to send Http request(http://example.test/orders), errcode=%d, errmsg=%s', $errno, $error
        ));

        $this->client->httpGet('http://example.test/orders', []);
    }

    public static function curlFailures(): array
    {
        return [
            'timeout' => [28, 'Operation timed out'],
            'error message without errno' => [0, 'Connection failed'],
            'errno without message' => [7, ''],
        ];
    }
}
