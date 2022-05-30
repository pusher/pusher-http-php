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

class SendToUserTest extends TestCase
{

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
            $this->pusher = new Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, ['cluster' => PUSHERAPP_CLUSTER]);
        }
    }

    public function testSendToUser(): void
    {
        $result = $this->pusher->sendToUser('123', 'my_event', 'Test string');
        self::assertEquals(new stdClass(), $result);
    }

    public function testSendToUserAsync(): void
    {
        $result = $this->pusher->sendToUserAsync('123', 'my_event', 'Test string')->wait();
        self::assertEquals(new stdClass(), $result);
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
