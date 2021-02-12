<?php

class PusherPushTest extends PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        if (PUSHERAPP_AUTHKEY === '' || PUSHERAPP_SECRET === '' || PUSHERAPP_APPID === '') {
            $this->markTestSkipped('Please set the
            PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET and
            PUSHERAPP_APPID keys.');
        } else {
            $this->pusher = new Pusher\Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, array(), PUSHERAPP_HOST);
            $this->pusher->setLogger(new TestLogger());
        }
    }

    public function testObjectConstruct()
    {
        $this->assertNotNull($this->pusher, 'Created new Pusher\Pusher object');
    }

    public function testStringPush()
    {
        $result = $this->pusher->trigger('test_channel', 'my_event', 'Test string');
        $this->assertEquals(new stdClass(), $result);
    }

    public function testArrayPush()
    {
        $result = $this->pusher->trigger('test_channel', 'my_event', array('test' => 1));
        $this->assertEquals(new stdClass(), $result);
    }

    public function testTLSPush()
    {
        $options = array(
            'useTLS' => true,
            'host'   => PUSHERAPP_HOST,
        );
        $pusher = new Pusher\Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, $options);
        $pusher->setLogger(new TestLogger());

        $result = $pusher->trigger('test_channel', 'my_event', array('encrypted' => 1));
        $this->assertEquals(new stdClass(), $result);
    }

    public function testSendingOver10kBMessageReturns413()
    {
        $this->expectException(\Pusher\ApiErrorException::class);
        $this->expectExceptionCode('413');

        $data = str_pad('', 11 * 1024, 'a');
        echo  'sending data of size: '.mb_strlen($data, '8bit');
        $this->pusher->trigger('test_channel', 'my_event', $data, null, true);
    }

    public function testTriggeringEventOnOver100ChannelsThrowsException()
    {
        $this->expectException(\Pusher\PusherException::class);

        $channels = array();
        while (count($channels) <= 101) {
            $channels[] = ('channel-'.count($channels));
        }
        $data = array('event_name' => 'event_data');
        $this->pusher->trigger($channels, 'my_event', $data);
    }

    public function testTriggeringEventOnMultipleChannels()
    {
        $data = array('event_name' => 'event_data');
        $channels = array('test_channel_1', 'test_channel_2');
        $result = $this->pusher->trigger($channels, 'my_event', $data);
        $this->assertEquals(new stdClass(), $result);
    }

    public function testTriggeringEventOnPrivateEncryptedChannelSuccess()
    {
        $options = array('encryption_master_key' => 'cAzRH3W9FZM3iXqSNIGtKztwNuCz9xMV');
        $this->pusher = new Pusher\Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, $options, PUSHERAPP_HOST);

        $data = array('event_name' => 'event_data');
        $channels = array('private-encrypted-ceppaio');
        $result = $this->pusher->trigger($channels, 'my_event', $data);
        $this->assertEquals(new stdClass(), $result);
    }

    public function testTriggeringEventOnMultipleChannelsWithEncryptedChannelPresentError()
    {
        $this->expectException(\Pusher\PusherException::class);

        $options = array('encryption_master_key' => 'cAzRH3W9FZM3iXqSNIGtKztwNuCz9xMV');
        $this->pusher = new Pusher\Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, $options, PUSHERAPP_HOST);

        $data = array('event_name' => 'event_data');
        $channels = array('my-chan-ceppaio', 'private-encrypted-ceppaio');
        $this->pusher->trigger($channels, 'my_event', $data);
    }
}
