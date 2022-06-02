<?php

namespace acceptance;

use GuzzleHttp;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;
use PHPUnit\Framework\TestCase;
use Pusher\ApiErrorException;
use Pusher\Pusher;
use Pusher\PusherException;
use stdClass;

class TerminateUserConnectionsTest extends TestCase
{
    private $request_history = [];

    /**
     * @var Pusher
     */
    private $pusher;

    protected function setUp(): void
    {
        if (PUSHERAPP_AUTHKEY === '' || PUSHERAPP_SECRET === '' || PUSHERAPP_APPID === '') {
            self::markTestSkipped('Please set the
            PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET and
            PUSHERAPP_APPID keys.');
        } else {
            $history = GuzzleHttp\Middleware::history($this->request_history);
            $handlerStack = GuzzleHttp\HandlerStack::create();
            $handlerStack->push($history);
            $httpClient = new GuzzleHttp\Client(['handler' => $handlerStack]);
            $this->pusher = new Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, ['cluster' => PUSHERAPP_CLUSTER], $httpClient);
        }
    }

    public function testTerminateUserConections(): void
    {
        $result = $this->pusher->terminateUserConnections("123");
        self::assertEquals(new stdClass(), $result);
        self::assertEquals(1, count($this->request_history));
        $request = $this->request_history[0]['request'];
        self::assertEquals('api-' . PUSHERAPP_CLUSTER . '.pusher.com', $request->GetUri()->GetHost());
        self::assertEquals('POST', $request->GetMethod());
        self::assertEquals('/apps/' . PUSHERAPP_APPID . '/users/123/terminate_connections', $request->GetUri()->GetPath());
    }

    public function testTerminateUserConectionsAsync(): void
    {
        $result = $this->pusher->terminateUserConnectionsAsync("123")->wait();
        self::assertEquals(new stdClass(), $result);
        self::assertEquals(1, count($this->request_history));
        $request = $this->request_history[0]['request'];
        self::assertEquals('api-' . PUSHERAPP_CLUSTER . '.pusher.com', $request->GetUri()->GetHost());
        self::assertEquals('POST', $request->GetMethod());
        self::assertEquals('/apps/' . PUSHERAPP_APPID . '/users/123/terminate_connections', $request->GetUri()->GetPath());
    }

    public function testBadUserId(): void
    {
        $this->expectException(PusherException::class);
        $this->pusher->terminateUserConnections("");
    }

    public function testBadUserIdAsync(): void
    {
        $this->expectException(PusherException::class);
        $this->pusher->terminateUserConnectionsAsync("");
    }
}
