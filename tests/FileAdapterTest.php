<?php

namespace Pusher\Tests;

use Pusher\Http\FileAdapter;

class FileAdapterTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        if (getenv('TRAVIS') || ini_get('allow_url_fopen') != '1') {
            $this->markTestSkipped();
        }
    }

    public function testIsSupported()
    {
        $this->assertTrue(FileAdapter::isSupported());
    }

    public function testRequest()
    {
        $Adapter = new FileAdapter(array(
            'http' => array('timeout' => 30),
        ));
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
    }

    public function testAdapterId()
    {
        $Adapter = new FileAdapter();
        $this->assertEquals('file/0.0.0', $Adapter->adapterId());
    }
}
