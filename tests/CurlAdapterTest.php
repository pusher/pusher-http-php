<?php

namespace Pusher\Tests;

use Pusher\Http\CurlAdapter;

class CurlAdapterTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        if (!extension_loaded('curl')) {
            $this->markTestSkipped();
        }
    }

    public function testIsSupported()
    {
        $this->assertTrue(CurlAdapter::isSupported());
    }

    public function testRequest()
    {
        $Adapter = new CurlAdapter(array(CURLOPT_TIMEOUT => 30));

        $result = $Adapter->request(
            'GET',
            'https://pusher.com',
            array(
                'User-Agent: pusher-php-test',
                'Accept: text/html',
                'Connection: keep-alive',
            ),
            null,
            10,
            null
        );
        $this->assertEquals(200, $result['status']);

        $result = $Adapter->request(
            'POST',
            'https://pusher.com',
            array(
                'User-Agent: pusher-php-test',
                'Accept: text/html',
                'Connection: keep-alive',
            ),
            array('foo' => 'bar'),
            10,
            null
        );
        $this->assertEquals(404, $result['status']);
        $this->assertContains('This ain\'t what you were looking for', $result['body']);

        unset($Adapter);
    }
}
