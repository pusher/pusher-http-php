<?php namespace Pusher;

use Mockery as m;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    const SERVER = 'http://fake-server.com';
    const PORT = 80;
    const AUTH_KEY = 'random-auth-key';
    const SECRET = 'auth-key-secret';
    const TIMEOUT = 30;

    public static $curl;

    private $client;

    public function setUp()
    {
        self::$curl = m::mock(new \stdClass());
    }

    public function tearDown()
    {
        m::close();
    }

    public function instantiate()
    {
        self::$curl->shouldReceive('setOpt')->once()->with('curl_mock', CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        self::$curl->shouldReceive('setOpt')->once()->with('curl_mock', CURLOPT_RETURNTRANSFER, 1);
        self::$curl->shouldReceive('setOpt')->once()->with('curl_mock', CURLOPT_TIMEOUT, self::TIMEOUT);
        self::$curl->shouldReceive('setOpt')->once()->with('curl_mock', CURLOPT_USERAGENT, Client::USER_AGENT . ' - v' . Pusher::VERSION);

        $this->client = new Client(self::SERVER, self::PORT, self::AUTH_KEY, self::SECRET, self::TIMEOUT);
    }

    public function testCanExecuteCurlGetMethodCall()
    {
        $this->instantiate();
        $params = array('hello' => 'pusher');
        $url = '/hello-world';

        // Mocking everything with re-defined functions
        $signedQuery = 'auth_key=random-auth-key&auth_signature=hmac_hash&auth_timestamp=1000&auth_version=1.0&hello=pusher';

        $fullUrl = self::SERVER . ':' . self::PORT . $url . '?' . $signedQuery;
        self::$curl->shouldReceive('setOpt')->once()->with('curl_mock', CURLOPT_URL, $fullUrl);
        self::$curl->shouldReceive('setOpt')->once()->with('curl_mock', CURLOPT_POST, 0);
        self::$curl->shouldReceive('getInfo')->once()->with('curl_mock', CURLINFO_HTTP_CODE)->andReturn(200);

        $this->assertEquals(array('body' => 'executed', 'status' => 200), $this->client->execute('GET', $url, $params));
    }

    public function testCanExecuteCurlPostMethodCall()
    {
        $this->instantiate();
        $params = array('hello' => 'pusher');
        $url = '/hello-world';
        $postFields = json_encode(array('key' => 'value'));

        // Mocking everything with re-defined functions
        $signedQuery = 'auth_key=random-auth-key&auth_signature=hmac_hash&auth_timestamp=1000&auth_version=1.0&hello=pusher';

        $fullUrl = self::SERVER . ':' . self::PORT . $url . '?' . $signedQuery;

        self::$curl->shouldReceive('setOpt')->once()->with('curl_mock', CURLOPT_URL, $fullUrl);
        self::$curl->shouldReceive('setOpt')->once()->with('curl_mock', CURLOPT_POST, 1);
        self::$curl->shouldReceive('setOpt')->once()->with('curl_mock', CURLOPT_POSTFIELDS, $postFields);
        self::$curl->shouldReceive('getInfo')->once()->with('curl_mock', CURLINFO_HTTP_CODE)->andReturn(200);

        $this->assertEquals(array('body' => 'executed', 'status' => 200), $this->client->execute('POST', $url, $params, $postFields));
    }

    public static function handleCurlSetOpt($curl, $opt, $value)
    {
        return self::$curl->setOpt($curl, $opt, $value);
    }

    public static function handleCurlGetInfo($curl, $info)
    {
        return self::$curl->getInfo($curl, $info);
    }
}