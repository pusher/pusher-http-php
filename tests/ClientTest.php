<?php

namespace Pusher\Tests;

use Pusher\Http\Client;
use Pusher\Config;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->c = new Client(new Config(array(
            'base_url' => 'http://a:b@foobar.com',
        )));
    }

    public function testConstrcutor()
    {
        $this->assertEquals('http://foobar.com', $this->c->baseUrl);
        $this->assertInstanceOf('Pusher\Http\Adapter', $this->c->adapter);
        $this->assertEquals(5, $this->c->timeout);
        $this->assertNull($this->c->proxyUrl);
        $this->assertInstanceOf('Pusher\KeyPair', $this->c->keyPair);
    }

    public function testRequest()
    {
        $this->c->adapter = $this->getMock('Pusher\Http\CurlAdapter', array('request'));
        $this->c->adapter
            ->expects($this->once())
            ->method('request')
            ->will($this->returnValue(array('status' => 200, 'body' => '{"data":"A Pusher message"}')));
        $result = $this->c->request('GET', '/bar/foo', array('bill' => 'ben'));
        $this->assertInstanceOf('\stdClass', $result);
        $this->assertEquals('A Pusher message', $result->data);

        $this->c->adapter = $this->getMock('Pusher\Http\CurlAdapter', array('request'));
        $this->c->adapter
            ->expects($this->once())
            ->method('request')
            ->will($this->returnValue(array('status' => 202)));
        $result = $this->c->request('GET', '/bar/foo', array('bill' => 'ben'));
        $this->assertTrue($result);
    }

    /**
     * @expectedException \Pusher\Exception\HTTPError
     * @expectedExceptionMessage Bad request
     */
    public function testRequest400Error()
    {
        $this->c->adapter = $this->getMock('Pusher\Http\CurlAdapter', array('request'));
        $this->c->adapter
            ->expects($this->once())
            ->method('request')
            ->will($this->returnValue(array('status' => 400)));
        $this->c->request('GET', '/bar/foo', array('bill' => 'ben'));
    }

    /**
     * @expectedException \Pusher\Exception\HTTPError
     * @expectedExceptionMessage Authentication error
     */
    public function testRequest401Error()
    {
        $this->c->adapter = $this->getMock('Pusher\Http\CurlAdapter', array('request'));
        $this->c->adapter
            ->expects($this->once())
            ->method('request')
            ->will($this->returnValue(array('status' => 401)));
        $this->c->request('GET', '/bar/foo', array('bill' => 'ben'));
    }

    /**
     * @expectedException \Pusher\Exception\HTTPError
     * @expectedExceptionMessage Not Found
     */
    public function testRequest404Error()
    {
        $this->c->adapter = $this->getMock('Pusher\Http\CurlAdapter', array('request'));
        $this->c->adapter
            ->expects($this->once())
            ->method('request')
            ->will($this->returnValue(array('status' => 404)));
        $this->c->request('GET', '/bar/foo', array('bill' => 'ben'));
    }

    /**
     * @expectedException \Pusher\Exception\HTTPError
     * @expectedExceptionMessage Proxy Authentication Required
     */
    public function testRequest407Error()
    {
        $this->c->adapter = $this->getMock('Pusher\Http\CurlAdapter', array('request'));
        $this->c->adapter
            ->expects($this->once())
            ->method('request')
            ->will($this->returnValue(array('status' => 407)));
        $this->c->request('GET', '/bar/foo', array('bill' => 'ben'));
    }

    /**
     * @expectedException \Pusher\Exception\HTTPError
     * @expectedExceptionMessage Unknown error
     */
    public function testRequestUnknownError()
    {
        $this->c->adapter = $this->getMock('Pusher\Http\CurlAdapter', array('request'));
        $this->c->adapter
            ->expects($this->once())
            ->method('request')
            ->will($this->returnValue(array('status' => 500)));
        $this->c->request('GET', '/bar/foo', array('bill' => 'ben'));
    }

    public function testGetRequest()
    {
        $this->c->adapter = $this->getMock('Pusher\Http\CurlAdapter', array('request'));
        $this->c->adapter
            ->expects($this->once())
            ->method('request')
            ->will($this->returnValue(array('status' => 200, 'body' => '{"data":"A Pusher message"}')));
        $result = $this->c->get('/foo/bar', array('bill' => 'ben'));
        $this->assertInstanceOf('\stdClass', $result);
        $this->assertEquals('A Pusher message', $result->data);
    }

    public function testPostRequest()
    {
        $this->c->adapter = $this->getMock('Pusher\Http\CurlAdapter', array('request'));
        $this->c->adapter
            ->expects($this->once())
            ->method('request')
            ->will($this->returnValue(array('status' => 200, 'body' => '{"data":"A Pusher message"}')));
        $result = $this->c->post('/foo/bar', array('bill' => 'ben'));
        $this->assertInstanceOf('\stdClass', $result);
        $this->assertEquals('A Pusher message', $result->data);
    }
}
