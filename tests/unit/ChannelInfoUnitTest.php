<?php

class ChannelInfoUnitTest extends PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        $this->pusher = new Pusher\Pusher('thisisaauthkey', 'thisisasecret', 1);
    }

    public function testTrailingColonChannelThrowsException()
    {
        $this->expectException(\Pusher\PusherException::class);

        $this->pusher->get_channel_info('test_channel:');
    }

    public function testLeadingColonChannelThrowsException()
    {
        $this->expectException(\Pusher\PusherException::class);

        $this->pusher->get_channel_info(':test_channel');
    }

    public function testLeadingColonNLChannelThrowsException()
    {
        $this->expectException(\Pusher\PusherException::class);
        
        $this->pusher->get_channel_info(':\ntest_channel');
    }

    public function testTrailingColonNLChannelThrowsException()
    {
        $this->expectException(\Pusher\PusherException::class);

        $this->pusher->get_channel_info('test_channel\n:');
    }
}
