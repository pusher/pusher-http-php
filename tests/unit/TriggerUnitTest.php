<?php

class TriggerUnitTest extends PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        $this->pusher = new Pusher\Pusher('thisisaauthkey', 'thisisasecret', 1);
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

        $this->pusher->trigger('test_channel:', $this->eventName, $this->data, array('socket_id' => '1.1:'));
    }

    public function testLeadingColonSocketIDThrowsException()
    {
        $this->expectException(\Pusher\PusherException::class);

        $this->pusher->trigger('test_channel:', $this->eventName, $this->data, array('socket_id' => ':1.1'));
    }

    public function testLeadingColonNLSocketIDThrowsException()
    {
        $this->expectException(\Pusher\PusherException::class);

        $this->pusher->trigger('test_channel:', $this->eventName, $this->data, array('socket_id' => ':\n1.1'));
    }

    public function testTrailingColonNLSocketIDThrowsException()
    {
        $this->expectException(\Pusher\PusherException::class);

        $this->pusher->trigger('test_channel:', $this->eventName, $this->data, array('socket_id' => '1.1\n:'));
    }

    public function testFalseSocketIDThrowsException()
    {
        $this->expectException(\Pusher\PusherException::class);

        $this->pusher->trigger('test_channel', $this->eventName, $this->data, array('socket_id' => false));
    }

    public function testEmptyStrSocketIDThrowsException()
    {
        $this->expectException(\Pusher\PusherException::class);

        $this->pusher->trigger('test_channel', $this->eventName, $this->data, array('socket_id' => ''));
    }
}
