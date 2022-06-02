<?php

namespace unit;

use GuzzleHttp;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;
use PHPUnit\Framework\TestCase;
use Pusher\ApiErrorException;
use Pusher\Pusher;
use Pusher\PusherException;
use stdClass;

class SendToUserTest extends TestCase
{
    private $request_history = [];

    private function mockPusher(array $responses): Pusher
    {
        $mockHandler = new GuzzleHttp\Handler\MockHandler($responses);
        $history = GuzzleHttp\Middleware::history($this->request_history);
        $handlerStack = GuzzleHttp\HandlerStack::create($mockHandler);
        $handlerStack->push($history);
        $httpClient = new GuzzleHttp\Client(['handler' => $handlerStack]);
        return new Pusher("auth-key", "secret", "appid", ['cluster' => 'test1'], $httpClient);
    }

    public function testSendUser(): void
    {
        $pusher = $this->mockPusher([new Response(200, [], "{}")]);
        $result = $pusher->sendToUser("123", "my-event", "event-data");
        self::assertEquals(new stdClass(), $result);
        self::assertEquals(1, count($this->request_history));
        $request = $this->request_history[0]['request'];
        self::assertEquals('api-test1.pusher.com', $request->GetUri()->GetHost());
        self::assertEquals('POST', $request->GetMethod());
        self::assertEquals('/apps/appid/events', $request->GetUri()->GetPath());
        self::assertEquals(
            '{"name":"my-event","data":"\"event-data\"","channel":"#server-to-user-123"}',
            (string) $request->GetBody()
        );
    }

    public function testBadUserId(): void
    {
        $pusher = $this->mockPusher([]);
        $this->expectException(PusherException::class);
        $pusher->sendToUser("", "my-event", "event data");
    }

    public function testBadUserIdAsync(): void
    {
        $pusher = $this->mockPusher([]);
        $this->expectException(PusherException::class);
        $pusher->sendToUserAsync("", "my-event", "event data");
    }
}
