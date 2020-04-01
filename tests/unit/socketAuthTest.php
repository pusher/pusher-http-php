<?php

class PusherSocketAuthTest extends PHPUnit\Framework\TestCase
{
    protected function setUp()
    {
        $this->pusher = new Pusher\Pusher('thisisaauthkey', 'thisisasecret', 1, true);
    }

    public function testObjectConstruct()
    {
        $this->assertNotNull($this->pusher, 'Created new Pusher\Pusher object');
    }

    public function testSocketAuthKey()
    {
        $socket_auth = $this->pusher->socket_auth('testing_pusher-php', '1.1');
        $this->assertEquals(
            $socket_auth,
            '{"auth":"thisisaauthkey:751ccc12aeaa79d46f7c199bced5fa47527d3480b51fe61a0bd10438241bd52d"}',
            'Socket auth key valid'
        );
    }

    public function testComplexSocketAuthKey()
    {
        $socket_auth = $this->pusher->socket_auth('-azAZ9_=@,.;', '45055.28877557');
        $this->assertEquals(
            $socket_auth,
            '{"auth":"thisisaauthkey:d1c20ad7684c172271f92c108e11b45aef07499b005796ae1ec5beb924f361c4"}',
            'Socket auth key valid'
        );
    }

    /**
     * @expectedException \Pusher\PusherException
     */
    public function testTrailingColonSocketIDThrowsException()
    {
        $this->pusher->socket_auth('testing_pusher-php', '1.1:');
    }

    /**
     * @expectedException \Pusher\PusherException
     */
    public function testLeadingColonSocketIDThrowsException()
    {
        $this->pusher->socket_auth('testing_pusher-php', ':1.1');
    }

    /**
     * @expectedException \Pusher\PusherException
     */
    public function testLeadingColonNLSocketIDThrowsException()
    {
        $this->pusher->socket_auth('testing_pusher-php', ':\n1.1');
    }

    /**
     * @expectedException \Pusher\PusherException
     */
    public function testTrailingColonNLSocketIDThrowsException()
    {
        $this->pusher->socket_auth('testing_pusher-php', '1.1\n:');
    }

    /**
     * @expectedException \Pusher\PusherException
     */
    public function testTrailingColonChannelThrowsException()
    {
        $this->pusher->socket_auth('test_channel:', '1.1');
    }

    /**
     * @expectedException \Pusher\PusherException
     */
    public function testLeadingColonChannelThrowsException()
    {
        $this->pusher->socket_auth(':test_channel', '1.1');
    }

    /**
     * @expectedException \Pusher\PusherException
     */
    public function testLeadingColonNLChannelThrowsException()
    {
        $this->pusher->socket_auth(':\ntest_channel', '1.1');
    }

    /**
     * @expectedException \Pusher\PusherException
     */
    public function testTrailingColonNLChannelThrowsException()
    {
        $this->pusher->socket_auth('test_channel\n:', '1.1');
    }
}
