<?php

namespace acceptance;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Pusher\Pusher;

class MiddlewareTest extends TestCase
{
    private $count = 0;
    /**
     * @var Pusher
     */
    private $pusher;

    public function increment(): Closure
    {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                $this->count++;
                return $handler($request, $options);
            };
        };
    }

    protected function setUp(): void
    {
        if (PUSHERAPP_AUTHKEY === '' || PUSHERAPP_SECRET === '' || PUSHERAPP_APPID === '') {
            self::markTestSkipped('Please set the
            PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET and
            PUSHERAPP_APPID keys.');
        } else {
            $stack = new HandlerStack();
            $stack->setHandler(new CurlHandler());
            $stack->push($this->increment());
            $client = new Client(['handler' => $stack]);
            $this->pusher = new Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, ['cluster' => PUSHERAPP_CLUSTER], $client);
        }
    }

    public function testStringPush(): void
    {
        self::assertEquals(0, $this->count);
        $result = $this->pusher->trigger('test_channel', 'my_event', 'Test string');
        self::assertEquals(1, $this->count);
    }
}
