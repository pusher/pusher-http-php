<?php

namespace PusherREST\Tests;

use PusherREST\KeyPair;

/**
* @covers PusherREST\KeyPair
*/
class KeyPairTest extends \PHPUnit_Framework_TestCase
{
    public function testSignesCorrectly()
    {
        $kp = new KeyPair('foo', 'bar');
        $this->assertEquals('zzz', $pk->sign('ahoi'));
    }

    // public function testUsesDefaultDefaultOptions()
    // {
    //     $client = new Client();
    //     $this->assertTrue($client->getDefaultOption('allow_redirects'));
    //     $this->assertTrue($client->getDefaultOption('exceptions'));
    //     $this->assertContains('cacert.pem', $client->getDefaultOption('verify'));
    // }
}
