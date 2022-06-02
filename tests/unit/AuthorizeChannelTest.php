<?php

namespace unit;

use PHPUnit\Framework\TestCase;
use Pusher\Pusher;
use Pusher\PusherException;

class AuthorizeChannelTest extends TestCase
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

    public function testAuthorizeChannel(): void
    {
        $auth_string = $this->pusher->authorizeChannel('testing_pusher-php', '1.1');
        self::assertEquals(
            '{"auth":"thisisaauthkey:751ccc12aeaa79d46f7c199bced5fa47527d3480b51fe61a0bd10438241bd52d"}',
            $auth_string,
            'Auth string key valid'
        );
    }

    public function testComplexAuthorizeChannel(): void
    {
        $auth_string = $this->pusher->authorizeChannel('-azAZ9_=@,.;', '45055.28877557');
        self::assertEquals(
            '{"auth":"thisisaauthkey:d1c20ad7684c172271f92c108e11b45aef07499b005796ae1ec5beb924f361c4"}',
            $auth_string,
            'Auth string key valid'
        );
    }

    public function testAuthorizeChannelWithChannelData(): void
    {
        $auth_string = $this->pusher->authorizeChannel('-azAZ9_=@,.;', '45055.28877557', '{"user_id": "123"}');
        self::assertEquals(
            '{"auth":"thisisaauthkey:3b3f1dcc4d7d2f95dd10ed05562397b3287b102d4cccfacbf30eed2f1ffa3d69","channel_data":"{\"user_id\": \"123\"}"}',
            $auth_string,
            'Auth string key valid'
        );
    }

    public function testTrailingColonSocketIDThrowsException(): void
    {
        $this->expectException(PusherException::class);

        $this->pusher->authorizeChannel('testing_pusher-php', '1.1:');
    }

    public function testLeadingColonSocketIDThrowsException(): void
    {
        $this->expectException(PusherException::class);

        $this->pusher->authorizeChannel('testing_pusher-php', ':1.1');
    }

    public function testLeadingColonNLSocketIDThrowsException(): void
    {
        $this->expectException(PusherException::class);

        $this->pusher->authorizeChannel('testing_pusher-php', ':\n1.1');
    }

    public function testTrailingColonNLSocketIDThrowsException(): void
    {
        $this->expectException(PusherException::class);

        $this->pusher->authorizeChannel('testing_pusher-php', '1.1\n:');
    }

    public function testTrailingColonChannelThrowsException(): void
    {
        $this->expectException(PusherException::class);

        $this->pusher->authorizeChannel('test_channel:', '1.1');
    }

    public function testLeadingColonChannelThrowsException(): void
    {
        $this->expectException(PusherException::class);

        $this->pusher->authorizeChannel(':test_channel', '1.1');
    }

    public function testLeadingColonNLChannelThrowsException(): void
    {
        $this->expectException(PusherException::class);

        $this->pusher->authorizeChannel(':\ntest_channel', '1.1');
    }

    public function testTrailingColonNLChannelThrowsException(): void
    {
        $this->expectException(PusherException::class);

        $this->pusher->authorizeChannel('test_channel\n:', '1.1');
    }
}
