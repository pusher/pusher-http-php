<?php

class AuthQueryStringTest extends PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        $this->pusher = new Pusher\Pusher('thisisaauthkey', 'thisisasecret', 1);
    }

    public function testArrayImplode()
    {
        $val = array('testKey' => 'testValue');

        $expected = 'testKey=testValue';
        $actual = Pusher\Pusher::array_implode('=', '&', $val);

        $this->assertEquals(
            $expected,
            $actual,
            'auth signature valid'
        );
    }

    public function testArrayImplodeWithTwoValues()
    {
        $val = array('testKey' => 'testValue', 'testKey2' => 'testValue2');

        $expected = 'testKey=testValue&testKey2=testValue2';
        $actual = Pusher\Pusher::array_implode('=', '&', $val);

        $this->assertEquals(
            $expected,
            $actual,
            'auth signature valid'
        );
    }

    public function testGenerateSignature()
    {
        $time = time();
        $auth_version = '1.0';
        $method = 'POST';
        $auth_key = 'thisisaauthkey';
        $auth_secret = 'thisisasecret';
        $request_path = '/channels/test_channel/events';
        $query_params = array(
            'name' => 'an_event',
        );
        $auth_query_string = Pusher\Pusher::build_auth_query_params(
            $auth_key,
            $auth_secret,
            $method,
            $request_path,
            $query_params,
            $auth_version,
            $time
        );

        $expected_to_sign = "POST\n$request_path\nauth_key=$auth_key&auth_timestamp=$time&auth_version=$auth_version&name=an_event";
        $expected_auth_signature = hash_hmac('sha256', $expected_to_sign, $auth_secret, false);
        $expected_query_params = [
            'auth_key' => $auth_key,
            'auth_signature' => $expected_auth_signature,
            'auth_timestamp' => $time,
            'auth_version' => $auth_version,
            'name' => 'an_event'
        ];

        $this->assertEquals(
            $expected_query_params,
            $auth_query_string,
            'auth signature valid'
        );
    }
}
