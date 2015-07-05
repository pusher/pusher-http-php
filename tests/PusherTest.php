<?php

namespace Pusher\Tests;

use Pusher\Pusher;

class PusherTest extends \PHPUnit_Framework_TestCase {

    /**
     * @expectedException Pusher\Exception\ConfigurationError
     */
    public function testExceptionWithEmptyFirstCall() {
        $pusher = Pusher::instance();
    }

    public function testGetSingleton() {
        $pusher = Pusher::instance('http://a:b@foobar.com');
        $this->assertTrue($pusher instanceof Pusher);

        $pusher2 = Pusher::instance();
        $this->assertTrue($pusher2 instanceof Pusher);
        $this->assertEquals($pusher->config->baseUrl, $pusher2->config->baseUrl);
    }

}
