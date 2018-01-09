<?php

class PusherLoggerTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->pusher = new Pusher\Pusher('', '', '');
    }


    public function tesSetRealLogger()
    {
        $this->pusher->set_logger(new TestLogger());
    }

    /**
     * @expectedException \Pusher\PusherException
     */
    public function testSetFakeLogger()
    {
        $this->pusher->set_logger(new FakeLogger());

    }

}
