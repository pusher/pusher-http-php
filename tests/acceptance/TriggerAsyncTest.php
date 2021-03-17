<?php

class TriggerAsyncTest extends PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        if (PUSHERAPP_AUTHKEY === '' || PUSHERAPP_SECRET === '' || PUSHERAPP_APPID === '') {
            $this->markTestSkipped('Please set the
            PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET and
            PUSHERAPP_APPID keys.');
        } else {
            $this->pusher = new Pusher\Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, ['host' => PUSHERAPP_HOST]);
        }
    }

    public function testObjectConstruct()
    {
        $this->assertNotNull($this->pusher, 'Created new Pusher\Pusher object');
    }

    public function testStringPush()
    {
        $result = $this->pusher->triggerAsync('test_channel', 'my_event', 'Test string')->wait();
        $this->assertEquals(new stdClass(), $result);
    }

    public function testArrayPush()
    {
        $result = $this->pusher->triggerAsync('test_channel', 'my_event', array('test' => 1))->wait();
        $this->assertEquals(new stdClass(), $result);
    }

    public function testPushWithSocketId()
    {
        $result = $this->pusher->triggerAsync('test_channel', 'my_event', array('test' => 1), array('socket_id' => '123.456'))->wait();
        $this->assertEquals(new stdClass(), $result);
    }

    public function testPushWithInfo()
    {
        $expectedMyChannel = new stdClass();
        $expectedMyChannel->subscription_count = 1;
        $expectedPresenceMyChannel = new stdClass();
        $expectedPresenceMyChannel->user_count = 0;
        $expectedPresenceMyChannel->subscription_count = 0;
        $expectedResult = new stdClass();
        $expectedResult->channels = array(
            "my-channel" => $expectedMyChannel,
            "presence-my-channel" => $expectedPresenceMyChannel,
        );

        $result = $this->pusher->triggerAsync(['my-channel', 'presence-my-channel'], 'my_event', array('test' => 1), array('info' => 'user_count,subscription_count'))->wait();
        $this->assertEquals($expectedResult, $result);
    }

    public function testTLSPush()
    {
        $options = array(
            'useTLS' => true,
            'host'   => PUSHERAPP_HOST,
        );
        $pusher = new Pusher\Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, $options);

        $result = $pusher->triggerAsync('test_channel', 'my_event', array('encrypted' => 1))->wait();
        $this->assertEquals(new stdClass(), $result);
    }

    public function testSendingOver10kBMessageReturns413()
    {
        $this->expectException(\Pusher\ApiErrorException::class);
        $this->expectExceptionCode('413');

        $data = str_pad('', 11 * 1024, 'a');
        $this->pusher->triggerAsync('test_channel', 'my_event', $data, array(), true)->wait();
    }

    public function testTriggeringEventOnOver100ChannelsThrowsException()
    {
        $this->expectException(\Pusher\PusherException::class);

        $channels = array();
        while (count($channels) <= 101) {
            $channels[] = ('channel-'.count($channels));
        }
        $data = array('event_name' => 'event_data');
        $this->pusher->triggerAsync($channels, 'my_event', $data)->wait();
    }

    public function testTriggeringEventOnMultipleChannels()
    {
        $data = array('event_name' => 'event_data');
        $channels = array('test_channel_1', 'test_channel_2');
        $result = $this->pusher->triggerAsync($channels, 'my_event', $data)->wait();
        $this->assertEquals(new stdClass(), $result);
    }

    public function testTriggeringEventOnPrivateEncryptedChannelSuccess()
    {
        $options = ['encryption_master_key_base64' => 'Y0F6UkgzVzlGWk0zaVhxU05JR3RLenR3TnVDejl4TVY=',
                    'host' => PUSHERAPP_HOST];
        $this->pusher = new Pusher\Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, $options);

        $data = array('event_name' => 'event_data');
        $channels = array('private-encrypted-ceppaio');
        $result = $this->pusher->triggerAsync($channels, 'my_event', $data)->wait();
        $this->assertEquals(new stdClass(), $result);
    }

    public function testTriggeringEventOnMultipleChannelsWithEncryptedChannelPresentError()
    {
        $this->expectException(\Pusher\PusherException::class);

        $options = ['encryption_master_key_base64' => 'Y0F6UkgzVzlGWk0zaVhxU05JR3RLenR3TnVDejl4TVY=',
                    'host' => PUSHERAPP_HOST];
        $this->pusher = new Pusher\Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, $options);

        $data = array('event_name' => 'event_data');
        $channels = array('my-chan-ceppaio', 'private-encrypted-ceppaio');
        $this->pusher->triggerAsync($channels, 'my_event', $data)->wait();
    }
}
