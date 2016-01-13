<?php

namespace Pusher\Tests;

use Pusher\Config;

/**
 * @covers pusher\Config
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{

    public function testSimpleConstructor()
    {
        $c = new Config('http://a:b@foobar.com');
        $this->assertEquals('http://foobar.com', $c->baseUrl);
        $c->validate();

        $this->assertEquals('a', $c->firstKeyPair()->key);
        $this->assertEquals('b', $c->firstKeyPair()->secret);

        $this->assertEquals(5, $c->timeout);
    }

    /**
     * @expectedException \Pusher\Exception\ConfigurationError
     * @expectedExceptionMessage You have not provided a valid configuration.
     */
    public function testInvalidConstructor()
    {
        $c = new Config(123456);
    }

    public function testDefaultUrlInConstructor()
    {
        $c = new Config(array(
            'app_id' => '1234',
            'keys' => array('a' => 'b'),
        ));
        $this->assertEquals('https://api.pusherapp.com/apps/1234', $c->baseUrl);

        $c = new Config(array(
            'app_id' => '1234',
            'keys' => array('a' => 'b'),
            'encrypted' => false,
        ));
        $this->assertEquals('http://api.pusherapp.com/apps/1234', $c->baseUrl);
    }

    public function testClusterUrl()
    {
        $c = new Config(array(
            'app_id' => '1234',
            'keys' => array('a' => 'b'),
            'cluster' => 'eu',
        ));
        $this->assertEquals('https://api-eu.pusher.com/apps/1234', $c->baseUrl);
    }

    /**
     * @expectedException \Pusher\Exception\ConfigurationError
     * @expectedExceptionMessage keys are missing.
     */
    public function testMissingKeysValidationError()
    {
        $c = new Config(array(
            'base_url' => 'http://foobar.com',
        ));
        $c->validate();
    }

    /**
     * @expectedException \Pusher\Exception\ConfigurationError
     * @expectedExceptionMessage baseUrl is missing.
     */
    public function testMissingBaseUrlError()
    {
        $c = new Config(array(
            'base_url' => 'http://a:b@foobar.com',
        ));
        $c->baseUrl = null;
        $c->validate();
    }

    /**
     * @expectedException \Pusher\Exception\ConfigurationError
     * @expectedExceptionMessage adapter is missing.
     */
    public function testMissingAdapterError()
    {
        $c = new Config(array(
            'base_url' => 'http://a:b@foobar.com',
        ));
        $c->adapter = null;
        $c->validate();
    }

    /**
     * @expectedException \Pusher\Exception\ConfigurationError
     * @expectedExceptionMessage timeout is not set.
     */
    public function testTimeoutConfigError()
    {
        $c = new Config(array(
            'base_url' => 'http://a:b@foobar.com',
            'adapter' => new \Pusher\Http\FileAdapter(),
        ));
        $c->timeout = null;
        $c->validate();
    }

    public function testTimeout()
    {
        $c = new Config(array(
            'base_url' => 'http://a:b@foobar.com',
            'timeout' => 30
        ));
        $c->validate();
        $this->assertEquals(30, $c->timeout);
    }

    public function testKeyPairs()
    {
        $c = new Config(array(
            'base_url' => 'http://a:b@foobar.com',
            'keys' => array(
                'c' => 'd'
            )
        ));

        // The first key is always the one from the URL
        $this->assertEquals('b', $c->firstKeyPair()->secret);
        $this->assertEquals('d', $c->keyPair('c')->secret);
    }

    public function testInstanceWithProxy()
    {
        $c = new Config(array(
            'base_url' => 'http://a:b@foobar.com',
            'proxy_url' => 'http://myproxy.com',
        ));

        $this->assertEquals('http://myproxy.com', $c->proxyUrl);
    }

    public function testSetBaseUrl()
    {
        $c = new Config(array(
            'base_url' => 'http://a:b@foobar.com',
        ));

        $this->assertFalse($c->setBaseUrl('http://'));

        $c->setBaseUrl('http://e:f@barfoo.com');
        $this->assertEquals('f', $c->keyPair('e')->secret);
        $this->assertEquals('http://barfoo.com', $c->baseUrl);
    }
}
