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

    /**
     * @expectedException \Pusher\Exceptions\PusherException
     */
    public function testThrowsExceptionIfChannelsCountIsMoreThanHundred()
    {
        $channels = array();
        foreach(range(0, 100) as $channel)
        {
            $channels[] = $channel;
        }

        $this->pusher->trigger($channels, 'event', 'data');
    }

    public function testCanTriggerEvent()
    {
        $expectedResponse = array('status' => 200);
        $payload = json_encode(array('name' => 'event', 'data' => json_encode('some_data'), 'channels' => array('channel')));

        $this->client->shouldReceive('post')->once()->with("/apps/fake-id/events", array('body_md5' => md5($payload)), $payload)->andReturn($expectedResponse);
        $this->assertTrue($this->pusher->trigger('channel', 'event', 'some_data'));
    }

    public function testCanTriggerEventAndReturnFalseOnFailure()
    {
        $expectedResponse = array('status' => 500);
        $payload = json_encode(array('name' => 'event', 'data' => json_encode('some_data'), 'channels' => array('channel')));

        $this->client->shouldReceive('post')->once()->with("/apps/fake-id/events", array('body_md5' => md5($payload)), $payload)->andReturn($expectedResponse);
        $this->assertFalse($this->pusher->trigger('channel', 'event', 'some_data'));
    }

    public function testCanGetArbitraryResource()
    {
        $response = array('status' => 200, 'body' => json_encode(array('hello' => 'world')));
        $expectedResult = array('status' => 200, 'body' => json_encode(array('hello' => 'world')), 'result' => array('hello' => 'world'));
        $payload = array('some' => 'payload');
        $this->client->shouldReceive('get')->once()->with('/apps/fake-id/some-path', $payload)->andReturn($response);

        $this->assertEquals($expectedResult, $this->pusher->get('/some-path', $payload));
    }

    public function testGetChannels()
    {
        $response = array('status' => 200, 'body' => json_encode(array('channels' => array('one' => 'name_one'))));
        $expectedResult = new \stdClass();
        $expectedResult->channels = array('one' => 'name_one');
        $payload = array('some' => 'payload');
        $this->client->shouldReceive('get')->once()->with('/apps/fake-id/channels', $payload)->andReturn($response);

        $this->assertEquals($expectedResult, $this->pusher->getChannels($payload));
    }

    public function testGetChannelInfo()
    {
        $response = array('status' => 200, 'body' => json_encode(array('channels' => array('one' => 'name_one'))));
        $expectedResult = json_decode($response['body']);
        $payload = array('some' => 'payload');
        $this->client->shouldReceive('get')->once()->with('/apps/fake-id/channels/fake-channel', $payload)->andReturn($response);

        $this->assertEquals($expectedResult, $this->pusher->getChannelInfo('fake-channel', $payload));
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

    public function testPresenceAuthWithUserInfo()
    {
        $expectedWithData = array(
            'auth' => 'fake-auth-key:hmac_hash',
            'channel_data' => json_encode(array(
                'user_id' => 'user-id',
                'user_info' => 'user-info'
            ))
        );
        $this->assertEquals(json_encode($expectedWithData), $this->pusher->presenceAuth('channel', 'socket-1', 'user-id', 'user-info'));
    }

    public function testPresenceAuthWithoutUserInfo()
    {
        $expectedWithData = array(
            'auth' => 'fake-auth-key:hmac_hash',
            'channel_data' => json_encode(array(
                'user_id' => 'user-id'
            ))
        );
        $this->assertEquals(json_encode($expectedWithData), $this->pusher->presenceAuth('channel', 'socket-1', 'user-id'));
    }
}
