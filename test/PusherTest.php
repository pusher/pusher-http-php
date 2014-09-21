<?php namespace Pusher;

use Mockery as m;

class PusherTest extends \PHPUnit_Framework_TestCase
{
    const AUTH_KEY = 'fake-auth-key';
    const SECRET = 'fake-secret';
    const APP_ID = 'fake-id';

    private $client;
    private $pusher;

    public function setUp()
    {
        $this->client = m::mock('Pusher\Client');
        $this->pusher = new Pusher(self::AUTH_KEY, self::SECRET, self::APP_ID, array(), null, $this->client);
    }

    public function tearDown()
    {
        m::close();
    }
    public function testSocketAuthWithCustomData()
    {
        $expectedWithData = array(
            'auth' => 'fake-auth-key:hmac_hash',
            'channel_data' => 'data'
        );
        $this->assertEquals(json_encode($expectedWithData), $this->pusher->socketAuth('channel', 'socket-1', 'data'));
    }

    public function testSocketAuthWithoutCustomData()
    {
        $expectedWithData = array(
            'auth' => 'fake-auth-key:hmac_hash',
        );
        $this->assertEquals(json_encode($expectedWithData), $this->pusher->socketAuth('channel', 'socket-1'));
    }
}
