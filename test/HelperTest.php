<?php namespace Pusher;

class HelperTest extends \PHPUnit_Framework_TestCase
{
    public function testCanBuildAuthQuery()
    {
        $authKey = 'key';
        $secret = 'secret';
        $requestMethod = 'GET';
        $path = '/hello';
        $params = array('key' => 'value');
        $authVersion = '2.0';
        $timestamp = 5000;

        $this->assertEquals('auth_key=key&auth_signature=hmac_hash&auth_timestamp=1000&auth_version=1.0&key=value',
                            Helper::buildAuthQuery($authKey, $secret, $requestMethod, $path, $params));

        $this->assertEquals('auth_key=key&auth_signature=hmac_hash&auth_timestamp=5000&auth_version=2.0&key=value',
                            Helper::buildAuthQuery($authKey, $secret, $requestMethod, $path, $params, $authVersion, $timestamp));
    }

    public function testCanCreateStringFromArray()
    {
        $data = array(
            'hello' => 'Pusher',
            'world' => array('table','keyboard')
        );

        $this->assertEquals('hello&Pusher=world&table,keyboard', Helper::arrayToString('&', '=', $data));
    }
}