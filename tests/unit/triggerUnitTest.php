<?php

class PusherTriggerUnitTest extends PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        $this->pusher = new Pusher\Pusher('thisisaauthkey', 'thisisasecret', 1, true);
        $this->eventName = 'test_event';
        $this->data = array();
    }

    public function testTrailingColonChannelThrowsException()
    {
        $this->expectException(\Pusher\PusherException::class);

        $this->pusher->trigger('test_channel:', $this->eventName, $this->data);
    }

    public function testLeadingColonChannelThrowsException()
    {
        $this->expectException(\Pusher\PusherException::class);

        $this->pusher->trigger(':test_channel', $this->eventName, $this->data);
    }

    public function testLeadingColonNLChannelThrowsException()
    {
        $this->expectException(\Pusher\PusherException::class);

        $this->pusher->trigger(':\ntest_channel', $this->eventName, $this->data);
    }

    public function testTrailingColonNLChannelThrowsException()
    {
        $this->expectException(\Pusher\PusherException::class);

        $this->pusher->trigger('test_channel\n:', $this->eventName, $this->data);
    }

    public function testChannelArrayThrowsException()
    {
        $this->expectException(\Pusher\PusherException::class);

        $this->pusher->trigger(array('this_one_is_okay', 'test_channel\n:'), $this->eventName, $this->data);
    }

    public function testTrailingColonSocketIDThrowsException()
    {
        $this->expectException(\Pusher\PusherException::class);

        $this->pusher->trigger('test_channel:', $this->eventName, $this->data, '1.1:');
    }

    public function testLeadingColonSocketIDThrowsException()
    {
        $this->expectException(\Pusher\PusherException::class);

        $this->pusher->trigger('test_channel:', $this->eventName, $this->data, ':1.1');
    }

    public function testLeadingColonNLSocketIDThrowsException()
    {
        $this->expectException(\Pusher\PusherException::class);

        $this->pusher->trigger('test_channel:', $this->eventName, $this->data, ':\n1.1');
    }

    public function testTrailingColonNLSocketIDThrowsException()
    {
        $this->expectException(\Pusher\PusherException::class);

        $this->pusher->trigger('test_channel:', $this->eventName, $this->data, '1.1\n:');
    }

    public function testNullSocketID()
    {
        // Check this does not throw an exception
        $this->pusher->trigger('test_channel', $this->eventName, $this->data, null);

        $this->assertTrue(true);
    }

    public function testFalseSocketIDThrowsException()
    {
        $this->expectException(\Pusher\PusherException::class);

        $this->pusher->trigger('test_channel', $this->eventName, $this->data, false);
    }

    public function testEmptyStrSocketIDThrowsException()
    {
        $this->expectException(\Pusher\PusherException::class);

        $this->pusher->trigger('test_channel', $this->eventName, $this->data, '');
    }
}
