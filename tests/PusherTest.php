<?php

namespace Pusher\Tests;

use Pusher\Pusher;

class PusherTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $Pusher = new Pusher('http://a:b@foobar.com');

        $this->assertInstanceOf('Pusher\Config', $Pusher->config);
        $this->assertInstanceOf('Pusher\Client', $Pusher->client);
    }

    public function testKeyPair()
    {
        $Pusher = new Pusher('http://a:b@foobar.com');

        $this->assertInstanceOf('Pusher\KeyPair', $Pusher->keyPair());
    }

    public function testAuthenticate()
    {
        $Pusher = new Pusher('http://a:b@foobar.com');
        $this->assertEquals(
            '{"auth":"a:a9e583c6fad5e38b69465bcff22fd809aac8b95fbdb9f7cae4bb27cb8e512f88"}',
            $Pusher->authenticate('38087.11062758', 'private-messages')
        );

        $this->assertEquals(
            '{"auth":"a:8c30292debfdfdbc169bc7866a979936c886ea9fd70eb397f1facd55da5e3d2a","channel_data":"\"channel-data\""}',
            $Pusher->authenticate('38087.11062758', 'private-messages', 'channel-data')
        );
    }

    public function testWebhook()
    {
        $_SERVER = array(
            'HTTP_X_PUSHER_KEY' => 'a',
            'HTTP_X_PUSHER_SIGNATURE' => 'sdfkjq2jnk12je',
        );

        $Pusher = new Pusher('http://a:b@foobar.com');
        $result = $Pusher->webhook(null, __DIR__ . '/body1.txt');

        $this->assertInstanceOf('Pusher\WebHook', $result);
        $this->assertEquals('sdfkjq2jnk12je', $result->signature);
        $this->assertInstanceOf('Pusher\KeyPair', $result->keyPair);

        unset($_SERVER);
    }

    public function testTrigger()
    {
        $Pusher = new Pusher('http://a:b@foobar.com');

        $Pusher->client = $this->getMock('Pusher\Client', array('post'), array('http://a:b@foobar.com'));
        $Pusher->client
            ->expects($this->exactly(2))
            ->method('post')
            ->withConsecutive(
                array(
                    $this->equalTo('events'),
                    $this->equalTo(
                        array(
                            'name' => 'new-message',
                            'data' => '{"body":"Hello"}',
                            'channels' => array('my-message'),
                        )
                    ),
                ),
                array(
                    $this->equalTo('events'),
                    $this->equalTo(
                        array(
                            'name' => 'new-message',
                            'data' => '{"body":"Hello"}',
                            'channels' => array('my-message'),
                            'socket_id' => '12345',
                        )
                    ),
                )

            );

        $Pusher->trigger('my-message', 'new-message', array('body' => 'Hello'));
        $Pusher->trigger('my-message', 'new-message', array('body' => 'Hello'), '12345');
    }

    /**
     * @expectedException \Pusher\Exception\Exception
     * @expectedExceptionMessage An event can be triggered on a maximum of 10 channels in a single call.
     */
    public function testTriggerThrowsError()
    {
        $Pusher = new Pusher('http://a:b@foobar.com');
        $Pusher->trigger(
            array(
                'my-message1',
                'my-message2',
                'my-message3',
                'my-message4',
                'my-message5',
                'my-message6',
                'my-message7',
                'my-message8',
                'my-message9',
                'my-message10',
                'my-message11',
            ),
            'new-message',
            array('body' => 'Hello')
        );
    }

    public function testChannels()
    {
        $Pusher = new Pusher('http://a:b@foobar.com');
        $Pusher->client = $this->getMock('Pusher\Client', array('get'), array('http://a:b@foobar.com'));
        $Pusher->client
            ->expects($this->once())
            ->method('get')
            ->with(
                $this->equalTo('/channels'),
                $this->equalTo(array('bill' => 'ben'))
            );
        $Pusher->channels(array('bill' => 'ben'));
    }

    public function testChannelInfo()
    {
        $Pusher = new Pusher('http://a:b@foobar.com');
        $Pusher->client = $this->getMock('Pusher\Client', array('get'), array('http://a:b@foobar.com'));
        $Pusher->client
            ->expects($this->once())
            ->method('get')
            ->with(
                $this->equalTo('/channels/foo'),
                $this->equalTo(array('bill' => 'ben'))
            );
        $Pusher->channelInfo('foo', array('bill' => 'ben'));
    }

    public function testPresenceUsers()
    {
        $Pusher = new Pusher('http://a:b@foobar.com');
        $Pusher->client = $this->getMock('Pusher\Client', array('get'), array('http://a:b@foobar.com'));
        $Pusher->client
            ->expects($this->once())
            ->method('get')
            ->with(
                $this->equalTo('/channels/foo/users'),
                $this->equalTo(array('bill' => 'ben'))
            );
        $Pusher->presenceUsers('foo', array('bill' => 'ben'));
    }
}
