<?php

namespace Pusher\Tests;

use Pusher\Client;

class ClientTest extends \PHPUnit_Framework_TestCase
{

    public function testConstrcutor()
    {
        $c = new Client(array(
            'base_url' => 'http://a:b@foobar.com',
        ));
        $this->assertEquals('http://foobar.com', $c->baseUrl);
        $this->assertInstanceOf('Pusher\HTTPAdapter', $c->adapter);
        $this->assertEquals(5, $c->timeout);
        $this->assertNull($c->proxyUrl);
        $this->assertInstanceOf('Pusher\KeyPair', $c->keyPair);
    }

    public function testRequest()
    {
        $c = new Client(array(
            'base_url' => 'http://a:b@foobar.com',
        ));
        $c->adapter = $this->getMock('Pusher\CurlAdapter', array('request'));
        $c->adapter
            ->expects($this->once())
            ->method('request')
            ->will($this->returnValue(array('status' => 200, 'body' => '{"data":"A Pusher message"}')));
        $result = $c->request('GET', '/bar/foo', array('bill' => 'ben'));
        $this->assertInstanceOf('\stdClass', $result);
        $this->assertEquals('A Pusher message', $result->data);

        $c->adapter = $this->getMock('Pusher\CurlAdapter', array('request'));
        $c->adapter
            ->expects($this->once())
            ->method('request')
            ->will($this->returnValue(array('status' => 202)));
        $result = $c->request('GET', '/bar/foo', array('bill' => 'ben'));
        $this->assertTrue($result);
    }

    /**
     * @expectedException \Pusher\Exception\HTTPError
     * @expectedExceptionMessage Bad request
     */
    public function testRequest400Error()
    {
        $c = new Client(array(
            'base_url' => 'http://a:b@foobar.com',
        ));
        $c->adapter = $this->getMock('Pusher\CurlAdapter', array('request'));
        $c->adapter
            ->expects($this->once())
            ->method('request')
            ->will($this->returnValue(array('status' => 400)));
        $c->request('GET', '/bar/foo', array('bill' => 'ben'));
    }

    /**
     * @expectedException \Pusher\Exception\HTTPError
     * @expectedExceptionMessage Authentication error
     */
    public function testRequest401Error()
    {
        $c = new Client(array(
            'base_url' => 'http://a:b@foobar.com',
        ));
        $c->adapter = $this->getMock('Pusher\CurlAdapter', array('request'));
        $c->adapter
            ->expects($this->once())
            ->method('request')
            ->will($this->returnValue(array('status' => 401)));
        $c->request('GET', '/bar/foo', array('bill' => 'ben'));
    }

    /**
     * @expectedException \Pusher\Exception\HTTPError
     * @expectedExceptionMessage Not Found
     */
    public function testRequest404Error()
    {
        $c = new Client(array(
            'base_url' => 'http://a:b@foobar.com',
        ));
        $c->adapter = $this->getMock('Pusher\CurlAdapter', array('request'));
        $c->adapter
            ->expects($this->once())
            ->method('request')
            ->will($this->returnValue(array('status' => 404)));
        $c->request('GET', '/bar/foo', array('bill' => 'ben'));
    }

    /**
     * @expectedException \Pusher\Exception\HTTPError
     * @expectedExceptionMessage Proxy Authentication Required
     */
    public function testRequest407Error()
    {
        $c = new Client(array(
            'base_url' => 'http://a:b@foobar.com',
        ));
        $c->adapter = $this->getMock('Pusher\CurlAdapter', array('request'));
        $c->adapter
            ->expects($this->once())
            ->method('request')
            ->will($this->returnValue(array('status' => 407)));
        $c->request('GET', '/bar/foo', array('bill' => 'ben'));
    }

    /**
     * @expectedException \Pusher\Exception\HTTPError
     * @expectedExceptionMessage Unknown error
     */
    public function testRequestUnknownError()
    {
        $c = new Client(array(
            'base_url' => 'http://a:b@foobar.com',
        ));
        $c->adapter = $this->getMock('Pusher\CurlAdapter', array('request'));
        $c->adapter
            ->expects($this->once())
            ->method('request')
            ->will($this->returnValue(array('status' => 500)));
        $c->request('GET', '/bar/foo', array('bill' => 'ben'));
    }

    public function testGetRequest()
    {
        $c = new Client(array(
            'base_url' => 'http://a:b@foobar.com',
        ));
        $c->adapter = $this->getMock('Pusher\CurlAdapter', array('request'));
        $c->adapter
            ->expects($this->once())
            ->method('request')
            ->will($this->returnValue(array('status' => 200, 'body' => '{"data":"A Pusher message"}')));
        $result = $c->get('/foo/bar', array('bill' => 'ben'));
        $this->assertInstanceOf('\stdClass', $result);
        $this->assertEquals('A Pusher message', $result->data);
    }

    public function testPostRequest()
    {
        $c = new Client(array(
            'base_url' => 'http://a:b@foobar.com',
        ));
        $c->adapter = $this->getMock('Pusher\CurlAdapter', array('request'));
        $c->adapter
            ->expects($this->once())
            ->method('request')
            ->will($this->returnValue(array('status' => 200, 'body' => '{"data":"A Pusher message"}')));
        $result = $c->post('/foo/bar', array('bill' => 'ben'));
        $this->assertInstanceOf('\stdClass', $result);
        $this->assertEquals('A Pusher message', $result->data);
    }
}
