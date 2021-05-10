<?php

namespace unit;

use PHPUnit\Framework\TestCase;
use Pusher\Pusher;
use Pusher\PusherException;

class SocketAuthTest extends TestCase
{
    /**
     * @var Pusher
     */
    private $pusher;

    protected function setUp(): void
    {
        $this->pusher = new Pusher('thisisaauthkey', 'thisisasecret', 1, []);
    }

    public function testObjectConstruct(): void
    {
        $this->assertNotNull($this->pusher, 'Created new \Pusher\Pusher object');
    }

    public function testSocketAuthKey(): void
    {
        $socket_auth = $this->pusher->socket_auth('testing_pusher-php', '1.1');
        self::assertEquals(
            '{"auth":"thisisaauthkey:751ccc12aeaa79d46f7c199bced5fa47527d3480b51fe61a0bd10438241bd52d"}',
            $socket_auth,
            'Socket auth key valid'
        );
    }

    public function testComplexSocketAuthKey(): void
    {
        $socket_auth = $this->pusher->socket_auth('-azAZ9_=@,.;', '45055.28877557');
        self::assertEquals(
            '{"auth":"thisisaauthkey:d1c20ad7684c172271f92c108e11b45aef07499b005796ae1ec5beb924f361c4"}',
            $socket_auth,
            'Socket auth key valid'
        );
    }

    public function testTrailingColonSocketIDThrowsException(): void
    {
        $this->expectException(PusherException::class);

        $this->pusher->socket_auth('testing_pusher-php', '1.1:');
    }

    public function testLeadingColonSocketIDThrowsException(): void
    {
        $this->expectException(PusherException::class);

        $this->pusher->socket_auth('testing_pusher-php', ':1.1');
    }

    public function testLeadingColonNLSocketIDThrowsException(): void
    {
        $this->expectException(PusherException::class);

        $this->pusher->socket_auth('testing_pusher-php', ':\n1.1');
    }

    public function testTrailingColonNLSocketIDThrowsException(): void
    {
        $this->expectException(PusherException::class);

        $this->pusher->socket_auth('testing_pusher-php', '1.1\n:');
    }

    public function testTrailingColonChannelThrowsException(): void
    {
        $this->expectException(PusherException::class);

        $this->pusher->socket_auth('test_channel:', '1.1');
    }

    public function testLeadingColonChannelThrowsException(): void
    {
        $this->expectException(PusherException::class);

        $this->pusher->socket_auth(':test_channel', '1.1');
    }

    public function testLeadingColonNLChannelThrowsException(): void
    {
        $this->expectException(PusherException::class);

        $this->pusher->socket_auth(':\ntest_channel', '1.1');
    }

    public function testTrailingColonNLChannelThrowsException(): void
    {
        $this->expectException(PusherException::class);

        $this->pusher->socket_auth('test_channel\n:', '1.1');
    }
}
