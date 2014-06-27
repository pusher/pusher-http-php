<?php

namespace PusherREST\Tests;

use PusherREST\Config;

/**
 * @covers PusherREST\Config
 */
class ConfigTest extends \PHPUnit_Framework_TestCase {

    public function testBase() {
        $c = new Config('http://foobar.com');
        $this->assertEquals('http://foobar.com', $c->apiUrl);
    }

}
