<?php

namespace Pusher\Tests;

use Pusher\Pusher;
use Pusher\Config;

class PusherTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->Pusher = new Pusher('1234', 'a', 'b', array(
            'base_url' => 'http://foobar.com',
        ));
    }

    /**
     * @expectedException \Pusher\Exception\ConfigurationException
     * @expectedExceptionMessage Missing app key and secret.
     */
    public function testMissingParamsError()
    {
        new Pusher('1234');
    }

    public function testConstructorWithConfigObject()
    {
        $config = new Config('http://a:b@foobar.com/apps/1234');
        $pusher = new Pusher($config);

        $this->assertEquals($config, $pusher->config);
    }

    public function testConstructor()
    {
        $this->assertInstanceOf('Pusher\Config', $this->Pusher->config);
        $this->assertInstanceOf('Pusher\Http\Client', $this->Pusher->httpClient);
    }

    public function testKeyPair()
    {
        $this->assertInstanceOf('Pusher\KeyPair', $this->Pusher->keyPair());
    }

    public function testAuthenticate()
    {
        $this->assertEquals(
            '{"auth":"a:a9e583c6fad5e38b69465bcff22fd809aac8b95fbdb9f7cae4bb27cb8e512f88"}',
            $this->Pusher->authenticate('38087.11062758', 'private-messages')
        );

        $this->assertEquals(
            '{"auth":"a:8c30292debfdfdbc169bc7866a979936c886ea9fd70eb397f1facd55da5e3d2a","channel_data":"\"channel-data\""}',
            $this->Pusher->authenticate('38087.11062758', 'private-messages', 'channel-data')
        );
    }

    public function testWebhook()
    {
        $_SERVER = array(
            'HTTP_X_PUSHER_KEY' => 'a',
            'HTTP_X_PUSHER_SIGNATURE' => 'sdfkjq2jnk12je',
        );

        $result = $this->Pusher->webhook(null, __DIR__ . '/body1.txt');

        $this->assertInstanceOf('Pusher\WebHook', $result);
        $this->assertEquals('sdfkjq2jnk12je', $result->signature);
        $this->assertInstanceOf('Pusher\KeyPair', $result->keyPair);

        unset($_SERVER);
    }

    public function testTrigger()
    {
        $config = new Config(array('base_url' => 'http://a:b@foobar.com'));
        $this->Pusher->httpClient = $this->getMock('Pusher\Http\Client', array('post'), array($config));
        $this->Pusher->httpClient
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

        $this->Pusher->trigger('my-message', 'new-message', array('body' => 'Hello'));
        $this->Pusher->trigger('my-message', 'new-message', array('body' => 'Hello'), '12345');
    }

    /**
     * @expectedException \Pusher\Exception\Exception
     * @expectedExceptionMessage An event can be triggered on a maximum of 10 channels in a single call.
     */
    public function testTriggerThrowsError()
    {
        $this->Pusher->trigger(
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
        $config = new Config(array('base_url' => 'http://a:b@foobar.com'));
        $this->Pusher->httpClient = $this->getMock('Pusher\Http\Client', array('get'), array($config));
        $this->Pusher->httpClient
            ->expects($this->once())
            ->method('get')
            ->with(
                $this->equalTo('/channels'),
                $this->equalTo(array('bill' => 'ben'))
            );
        $this->Pusher->channels(array('bill' => 'ben'));
    }

    public function testChannelInfo()
    {
        $config = new Config(array('base_url' => 'http://a:b@foobar.com'));
        $this->Pusher->httpClient = $this->getMock('Pusher\Http\Client', array('get'), array($config));
        $this->Pusher->httpClient
            ->expects($this->once())
            ->method('get')
            ->with(
                $this->equalTo('/channels/foo'),
                $this->equalTo(array('bill' => 'ben'))
            );
        $this->Pusher->channelInfo('foo', array('bill' => 'ben'));
    }

    public function testPresenceUsers()
    {
        $config = new Config(array('base_url' => 'http://a:b@foobar.com'));
        $this->Pusher->httpClient = $this->getMock('Pusher\Http\Client', array('get'), array($config));
        $this->Pusher->httpClient
            ->expects($this->once())
            ->method('get')
            ->with(
                $this->equalTo('/channels/foo/users'),
                $this->equalTo(array('bill' => 'ben'))
            );
        $this->Pusher->presenceUsers('foo', array('bill' => 'ben'));
    }
}
