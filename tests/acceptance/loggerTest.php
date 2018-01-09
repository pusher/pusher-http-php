<?php

class PusherLoggerTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->pusher = new Pusher\Pusher('', '', '');
    }

    /**
     * @expectedException \Pusher\PusherException
     */
    public function tesSetRealLogger()
    {
        $this->pusher->set_logger(new TestLogger());
    }

    public function testSetFakeLogger()
    {
        $this->pusher->set_logger(new FakeLogger());
    }
}
