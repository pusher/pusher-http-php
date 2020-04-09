<?php

class PusherPushTest extends PHPUnit\Framework\TestCase
{
    protected function setUp()
    {
        if (PUSHERAPP_AUTHKEY === '' || PUSHERAPP_SECRET === '' || PUSHERAPP_APPID === '') {
            $this->markTestSkipped('Please set the
            PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET and
            PUSHERAPP_APPID keys.');
        } else {
            $this->pusher = new Pusher\Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, false, PUSHERAPP_HOST);
            $this->pusher->setLogger(new TestLogger());
        }
    }

    public function testObjectConstruct()
    {
        $this->assertNotNull($this->pusher, 'Created new Pusher\Pusher object');
    }

    public function testStringPush()
    {
        $string_trigger = $this->pusher->trigger('test_channel', 'my_event', 'Test string');
        $this->assertTrue($string_trigger, 'Trigger with string payload');
    }

    public function testArrayPush()
    {
        $structure_trigger = $this->pusher->trigger('test_channel', 'my_event', array('test' => 1));
        $this->assertTrue($structure_trigger, 'Trigger with structured payload');
    }

    public function testTLSPush()
    {
        $options = array(
            'useTLS' => true,
            'host'   => PUSHERAPP_HOST,
        );
        $pusher = new Pusher\Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, $options);
        $pusher->setLogger(new TestLogger());

        $structure_trigger = $pusher->trigger('test_channel', 'my_event', array('encrypted' => 1));
        $this->assertTrue($structure_trigger, 'Trigger with over TLS connection');
    }

    public function testSendingOver10kBMessageReturns413()
    {
        $data = str_pad('', 11 * 1024, 'a');
        echo  'sending data of size: '.mb_strlen($data, '8bit');
        $response = $this->pusher->trigger('test_channel', 'my_event', $data, null, true);
        $this->assertEquals(413, $response['status'], '413 HTTP status response expected');
    }

    /**
     * @expectedException \Pusher\PusherException
     */
    public function testTriggeringEventOnOver100ChannelsThrowsException()
    {
        $channels = array();
        while (count($channels) <= 101) {
            $channels[] = ('channel-'.count($channels));
        }
        $data = array('event_name' => 'event_data');
        $response = $this->pusher->trigger($channels, 'my_event', $data);
    }

    public function testTriggeringEventOnMultipleChannels()
    {
        $data = array('event_name' => 'event_data');
        $channels = array('test_channel_1', 'test_channel_2');
        $response = $this->pusher->trigger($channels, 'my_event', $data);

        $this->assertTrue($response);
    }

    public function testTriggeringEventOnPrivateEncryptedChannelSuccess()
    {
        $options = array('encryption_master_key' => 'cAzRH3W9FZM3iXqSNIGtKztwNuCz9xMV');
        $this->pusher = new Pusher\Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, $options, PUSHERAPP_HOST);

        $data = array('event_name' => 'event_data');
        $channels = array('private-encrypted-ceppaio');
        $response = $this->pusher->trigger($channels, 'my_event', $data);

        $this->assertTrue($response);
    }

    /**
     * @expectedException \Pusher\PusherException
     */
    public function testTriggeringEventOnMultipleChannelsWithEncryptedChannelPresentError()
    {
        $options = array('encryption_master_key' => 'cAzRH3W9FZM3iXqSNIGtKztwNuCz9xMV');
        $this->pusher = new Pusher\Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, $options, PUSHERAPP_HOST);

        $data = array('event_name' => 'event_data');
        $channels = array('my-chan-ceppaio', 'private-encrypted-ceppaio');
        $response = $this->pusher->trigger($channels, 'my_event', $data);
    }
}
