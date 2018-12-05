<?php

class PusherChannelInfoUnitTest extends PHPUnit\Framework\TestCase
{
    protected function setUp()
    {
        $this->pusher = new Pusher\Pusher('thisisaauthkey', 'thisisasecret', 1, true);
    }

    /**
     * @expectedException \Pusher\PusherException
     */
    public function testTrailingColonChannelThrowsException()
    {
        $this->pusher->get_channel_info('test_channel:');
    }

    /**
     * @expectedException \Pusher\PusherException
     */
    public function testLeadingColonChannelThrowsException()
    {
        $this->pusher->get_channel_info(':test_channel');
    }

    /**
     * @expectedException \Pusher\PusherException
     */
    public function testLeadingColonNLChannelThrowsException()
    {
        $this->pusher->get_channel_info(':\ntest_channel');
    }

    /**
     * @expectedException \Pusher\PusherException
     */
    public function testTrailingColonNLChannelThrowsException()
    {
        $this->pusher->get_channel_info('test_channel\n:');
    }
}
