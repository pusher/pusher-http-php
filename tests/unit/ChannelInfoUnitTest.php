<?php

namespace unit;

use PHPUnit\Framework\TestCase;
use Pusher\Pusher;

class ChannelInfoUnitTest extends TestCase
{
    /**
     * @var Pusher
     */
    private $pusher;

    protected function setUp(): void
    {
        $this->pusher = new Pusher('thisisaauthkey', 'thisisasecret', 1);
    }

    public function testTrailingColonChannelThrowsException(): void
    {
        $this->expectException(\Pusher\PusherException::class);

        $this->pusher->get_channel_info('test_channel:');
    }

    public function testLeadingColonChannelThrowsException(): void
    {
        $this->expectException(\Pusher\PusherException::class);

        $this->pusher->get_channel_info(':test_channel');
    }

    public function testLeadingColonNLChannelThrowsException(): void
    {
        $this->expectException(\Pusher\PusherException::class);

        $this->pusher->get_channel_info(':\ntest_channel');
    }

    public function testTrailingColonNLChannelThrowsException(): void
    {
        $this->expectException(\Pusher\PusherException::class);

        $this->pusher->get_channel_info('test_channel\n:');
    }
}
