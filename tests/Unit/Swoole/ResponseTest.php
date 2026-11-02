<?php

namespace Hhxsv5\LaravelS\Tests\Unit\Swoole;

use Hhxsv5\LaravelS\Swoole\DynamicResponse;
use Hhxsv5\LaravelS\Tests\TestCase;
use Swoole\Http\Response as SwooleResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class ResponseTest extends TestCase
{
    public function testSendsStatusCode(): void
    {
        $target = $this->createMock(SwooleResponse::class);
        $target->expects($this->once())->method('status')->with(202)->willReturn(true);

        (new DynamicResponse($target, new Response('', 202)))->sendStatusCode();
    }

    public function testSendsMultipleHeaderValuesAndSeparatesTrailers(): void
    {
        $source = new Response();
        $source->headers->set('X-Trace', ['first', 'second']);
        // The adapter consumes the lower-case trailer declaration emitted by Swoole.
        $source->headers->set('trailer', ['checksum']);
        $source->headers->set('checksum', ['abc', 'def']);
        $source->headers->setCookie(new Cookie('session', 'value'));
        $headers = [];
        $trailers = [];
        $target = $this->createMock(SwooleResponse::class);
        $target->method('header')->willReturnCallback(function ($name, $values) use (&$headers) {
            $headers[$name] = $values;
            return true;
        });
        $target->expects($this->exactly(2))->method('trailer')->willReturnCallback(function ($name, $value) use (&$trailers) {
            $trailers[] = [$name, $value];
            return true;
        });
        $response = new DynamicResponse($target, $source);

        $response->sendHeaders();
        $response->sendTrailers();

        $this->assertSame(['first', 'second'], $headers['X-Trace']);
        $this->assertSame(['checksum'], $headers['trailer']);
        $this->assertArrayNotHasKey('checksum', $headers);
        $this->assertArrayNotHasKey('Set-Cookie', $headers);
        $this->assertSame([['checksum', 'abc'], ['checksum', 'def']], $trailers);
    }

    public function testSendsCookieAttributesAndUsesRawCookieForRawValues(): void
    {
        $source = new Response();
        $source->headers->setCookie(new Cookie('session', 'a b', 2000000000, '/app', 'example.test', true, true, false, 'lax'));
        $source->headers->setCookie(new Cookie('raw', 'a+b', 0, '/', null, false, false, true, 'strict'));
        $target = $this->createMock(SwooleResponse::class);
        $target->expects($this->once())->method('cookie')
            ->with('session', 'a b', 2000000000, '/app', 'example.test', true, true, 'lax')->willReturn(true);
        $target->expects($this->once())->method('rawcookie')
            ->with('raw', 'a+b', 0, '/', '', false, false, 'strict')->willReturn(true);

        (new DynamicResponse($target, $source))->sendCookies();
    }

    public function testSendEmitsMetadataBeforeContent(): void
    {
        $source = new Response('body', 201, ['X-Trace' => 'trace']);
        $source->headers->setCookie(new Cookie('session', 'value'));
        $calls = [];
        $target = $this->createMock(SwooleResponse::class);
        foreach (['status', 'header', 'cookie', 'end'] as $method) {
            $target->method($method)->willReturnCallback(function (...$args) use (&$calls, $method) {
                $calls[] = [$method, $args];
                return true;
            });
        }

        (new DynamicResponse($target, $source))->send();

        $methods = array_column($calls, 0);
        $this->assertSame('status', $calls[0][0]);
        $this->assertSame(201, $calls[0][1][0]);
        $this->assertSame('end', $calls[count($calls) - 1][0]);
        $this->assertSame('body', $calls[count($calls) - 1][1][0]);
        $this->assertContains('header', $methods);
        $this->assertContains('cookie', $methods);
        $this->assertLessThan(array_search('cookie', $methods), array_search('header', $methods));
    }
}
