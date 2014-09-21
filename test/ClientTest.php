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