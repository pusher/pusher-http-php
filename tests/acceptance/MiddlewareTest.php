<?php

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;
use Psr\Http\Message\RequestInterface;

class MiddlewareTest extends PHPUnit\Framework\TestCase
{
    private $count = 0;
    function increment()
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
            $this->markTestSkipped('Please set the
            PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET and
            PUSHERAPP_APPID keys.');
        } else {
            $stack = new HandlerStack();
            $stack->setHandler(new CurlHandler());
            $stack->push($this->increment());
            $client = new Client(['handler' => $stack]);
            $this->pusher = new Pusher\Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, ['host' => PUSHERAPP_HOST], $client);
        }
    }

    public function testStringPush()
    {
        $this->assertEquals(0, $this->count);
        $result = $this->pusher->trigger('test_channel', 'my_event', 'Test string');
        $this->assertEquals(1, $this->count);
    }
}
