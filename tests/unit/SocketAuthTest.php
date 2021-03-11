<?php

class SocketAuthTest extends PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        $this->pusher = new Pusher\Pusher('thisisaauthkey', 'thisisasecret', 1, array());
    }

    public function testObjectConstruct()
    {
        $this->assertNotNull($this->pusher, 'Created new Pusher\Pusher object');
    }

    public function testSocketAuthKey()
    {
        $socket_auth = $this->pusher->socket_auth('testing_pusher-php', '1.1');
        $this->assertEquals(
            '{"auth":"thisisaauthkey:751ccc12aeaa79d46f7c199bced5fa47527d3480b51fe61a0bd10438241bd52d"}',
            $socket_auth,
            'Socket auth key valid'
        );
    }

    public function testComplexSocketAuthKey()
    {
        $socket_auth = $this->pusher->socket_auth('-azAZ9_=@,.;', '45055.28877557');
        $this->assertEquals(
            '{"auth":"thisisaauthkey:d1c20ad7684c172271f92c108e11b45aef07499b005796ae1ec5beb924f361c4"}',
            $socket_auth,
            'Socket auth key valid'
        );
    }

    public function testTrailingColonSocketIDThrowsException()
    {
        $this->expectException(\Pusher\PusherException::class);

        $this->pusher->socket_auth('testing_pusher-php', '1.1:');
    }

    public function testLeadingColonSocketIDThrowsException()
    {
        $this->expectException(\Pusher\PusherException::class);

        $this->pusher->socket_auth('testing_pusher-php', ':1.1');
    }

    public function testLeadingColonNLSocketIDThrowsException()
    {
        $this->expectException(\Pusher\PusherException::class);

        $this->pusher->socket_auth('testing_pusher-php', ':\n1.1');
    }

    public function testTrailingColonNLSocketIDThrowsException()
    {
        $this->expectException(\Pusher\PusherException::class);

        $this->pusher->socket_auth('testing_pusher-php', '1.1\n:');
    }

    public function testTrailingColonChannelThrowsException()
    {
        $this->expectException(\Pusher\PusherException::class);

        $this->pusher->socket_auth('test_channel:', '1.1');
    }

    public function testLeadingColonChannelThrowsException()
    {
        $this->expectException(\Pusher\PusherException::class);

        $this->pusher->socket_auth(':test_channel', '1.1');
    }

    public function testLeadingColonNLChannelThrowsException()
    {
        $this->expectException(\Pusher\PusherException::class);

        $this->pusher->socket_auth(':\ntest_channel', '1.1');
    }

    public function testTrailingColonNLChannelThrowsException()
    {
        $this->expectException(\Pusher\PusherException::class);

        $this->pusher->socket_auth('test_channel\n:', '1.1');
    }
}
