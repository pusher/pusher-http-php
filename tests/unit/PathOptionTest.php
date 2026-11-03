<?php

namespace unit;

use GuzzleHttp;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Pusher\Pusher;

class PathOptionTest extends TestCase
{
    private $request_history = [];

    private function mockPusher(array $options): Pusher
    {
        $mockHandler = new GuzzleHttp\Handler\MockHandler([
            new Response(200, [], '{"channels":{}}'),
            new Response(200, [], '{}'),
            new Response(200, [], '{}'),
        ]);
        $handlerStack = GuzzleHttp\HandlerStack::create($mockHandler);
        $handlerStack->push(GuzzleHttp\Middleware::history($this->request_history));
        $httpClient = new GuzzleHttp\Client(['handler' => $handlerStack]);

        return new Pusher('auth-key', 'secret', 'appid', $options, $httpClient);
    }

    public function pathProvider(): array
    {
        return [
            'no path' => ['', '/apps/appid'],
            'path without trailing slash' => ['/pusher', '/pusher/apps/appid'],
            'path with trailing slash' => ['/pusher/', '/pusher/apps/appid'],
        ];
    }

    /**
     * @dataProvider pathProvider
     */
    public function testPathOptionIsPrependedToRequestPaths(string $path, string $expected_prefix): void
    {
        $pusher = $this->mockPusher(['host' => 'proxy.example.com', 'path' => $path]);

        $pusher->getChannels();
        $pusher->trigger('test-channel', 'my-event', 'data');
        $pusher->triggerAsync('test-channel', 'my-event', 'data')->wait();

        self::assertCount(3, $this->request_history);
        self::assertEquals($expected_prefix . '/channels', $this->request_history[0]['request']->getUri()->getPath());
        self::assertEquals($expected_prefix . '/events', $this->request_history[1]['request']->getUri()->getPath());
        self::assertEquals($expected_prefix . '/events', $this->request_history[2]['request']->getUri()->getPath());
        foreach ($this->request_history as $transaction) {
            self::assertEquals('proxy.example.com', $transaction['request']->getUri()->getHost());
        }
    }
}
