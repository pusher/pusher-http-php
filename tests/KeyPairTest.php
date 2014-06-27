<?php

namespace PusherREST\Tests;

use PusherREST\KeyPair;

/**
 * @covers PusherREST\KeyPair
 * */
class KeyPairTest extends \PHPUnit_Framework_TestCase {

    public function testSign() {
        $kp = new KeyPair('foo', 'bar');
        $signature = $kp->sign('ahoi');
        $this->assertEquals('a66ed82a8fe9d4534399025aea6784cab7f7da21064c122b49ca63037af1f584', $signature);
    }

    public function testPrivateChannelSignature() {
        $kp = new KeyPair('foo', 'bar');
        $signature = $kp->channelSignature('socket_id', 'channel');
        $this->assertEquals('f475e006523442c5a2414ada387befda40a1a7a7a36112a34c21082c9513386f', $signature);
    }

    public function testPresenceChannelSignature() {
        $kp = new KeyPair('foo', 'bar');
        $signature = $kp->channelSignature('socket_id', 'channel', '{"user_id":30}');
        $this->assertEquals('50f91607499f4baae8d4e6f4c15dc6c745c3244c6bde95c7b9ec1cea1e82e6f8', $signature);
    }

    public function testSignedParamsWithNullBody() {
        $kp = new KeyPair('foo', 'bar');
        $valid_params = array(
            'auth_key' => 'foo',
            'auth_timestamp' => 1337,
            'auth_version' => '1.0',
            'auth_signature' => '5c0e3fb4b331ee3ad978d9ab089e5dc59856c5df581b315be7611edeaa387e0d',
        );
        $params = $kp->signedParams('GET', '/some/path', array('auth_timestamp' => 1337), null);
        $this->assertEquals($valid_params, $params);

        $params = $kp->signedParams('GET', '/some/path', array(), null);
        $this->assertTrue($params['auth_timestamp'] > 1403116543);

        $valid_params = array(
            'auth_key' => 'foo',
            'auth_timestamp' => 1337,
            'auth_version' => '1.0',
            'auth_signature' => '525aaa926c5503a3f955a74f236fa93e284cf3ccfd72a043ffebc15e29a52d52',
            'body_md5' => '9f9d51bc70ef21ca5c14f307980a29d8',
        );
        $params = $kp->signedParams('GET', '/some/path', array('auth_timestamp' => 1337), "bob");
        $this->assertEquals($valid_params, $params);
    }

    public function testVerify() {
        $kp = new KeyPair('foo', 'bar');
        $signature = $kp->sign('ahoi');
        $this->assertTrue($kp->verify($signature, 'ahoi'));
        $this->assertFalse($kp->verify($signature, 'ahoi2'));
    }

    public function testConstantCompare() {
        $this->assertTrue(\PusherREST\constant_compare("foo", "foo"));
        $this->assertFalse(\PusherREST\constant_compare("fo", "foo"));
        $this->assertFalse(\PusherREST\constant_compare("foo", "fo"));
    }

}
