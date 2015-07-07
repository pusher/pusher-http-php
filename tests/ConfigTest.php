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
     * @expectedException pusher\Exception\ConfigurationError
     */
    public function testValidationError()
    {
        $c = new Config();
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
}
