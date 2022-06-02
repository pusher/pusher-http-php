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

class TerminateUserConnectionsUnitTest extends TestCase
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

    public function testTerminateUserConections(): void
    {
        $pusher = $this->mockPusher([new Response(200, [], "{}")]);
        $result = $pusher->terminateUserConnections("123");
        self::assertEquals(new stdClass(), $result);
        self::assertEquals(1, count($this->request_history));
        $request = $this->request_history[0]['request'];
        self::assertEquals('api-test1.pusher.com', $request->GetUri()->GetHost());
        self::assertEquals('POST', $request->GetMethod());
        self::assertEquals('/apps/appid/users/123/terminate_connections', $request->GetUri()->GetPath());
    }

    public function testTerminateUserConectionsAsync(): void
    {
        $pusher = $this->mockPusher([new Response(200, [], "{}")]);
        $result = $pusher->terminateUserConnectionsAsync("123")->wait();
        self::assertEquals(new stdClass(), $result);
        self::assertEquals(1, count($this->request_history));
        $request = $this->request_history[0]['request'];
        self::assertEquals('api-test1.pusher.com', $request->GetUri()->GetHost());
        self::assertEquals('POST', $request->GetMethod());
        self::assertEquals('/apps/appid/users/123/terminate_connections', $request->GetUri()->GetPath());
    }

    public function testBadUserId(): void
    {
        $pusher = $this->mockPusher([]);
        $this->expectException(PusherException::class);
        $pusher->terminateUserConnections("");
    }

    public function testBadUserIdAsync(): void
    {
        $pusher = $this->mockPusher([]);
        $this->expectException(PusherException::class);
        $pusher->terminateUserConnectionsAsync("");
    }

    public function testTerminateUserConectionsError(): void
    {
        $pusher = $this->mockPusher([new Response(500, [], "{}")]);
        $this->expectException(ApiErrorException::class);
        $pusher->terminateUserConnections("123");
    }

    public function testTerminateUserConectionsAsyncError(): void
    {
        $pusher = $this->mockPusher([new Response(500, [], "{}")]);
        $this->expectException(ApiErrorException::class);
        $pusher->terminateUserConnectionsAsync("123")->wait();
    }
}
