<?php namespace PusherREST;

use PusherREST\VERSION;
use PusherREST\CurlAdapter;
use PusherREST\FileAdapter;

if (!extension_loaded('json')) {
    throw new Exception('There is missing dependant extensions - please ensure that the JSON module is installed');
}

class Client
{
    /** @var string **/
    public $base_url;

    /** @var HTTPAdapterInterface **/
    public $adapter;

    /** @var int in seconds **/
    public $timeout;

    /** @var PusherREST\KeyPair **/
    public $key_pair;


    /**
     * @param $config PusherREST\Config
     **/
    public function __construct($config = PusherREST::config)
    {
        $this->base_url = $config->api_url;
        $this->adapter = $config->api_adapter;
        $this->timeout = $config->api_timeout;
        $this->key_pair = $config->key_pair;
    }

    /**
     * @param $method string
     * @param $path string
     * @param $params array
     * @param $body array|null
     **/
    public function request($method, $path, $params = array(), $body = null)
    {
        $method = strtoupper($method);
        if (!is_null($body)) {
            $body = json_encode($body);
        }
        $params = $this->signedParams($method, $path, $params, $body);
        $response = $this->adapter->request(
            $method,
            $this->base_url . $path . '?' . http_build_query($params),
            $this->requestHeaders(),
            $body);
        // TODO: handle bad requests
        return json_decode($response['body']);
    }

    public function get($path, $params)
    {
        return $this->request('GET', $path, $params);
    }

    public function post($path, $body)
    {
        return $this->request('POST', $path, array(), $body);
    }

    public function trigger($channels, $event, $data, $socket_id = null, $already_encoded = false)
    {
        if (is_string( $channels )) {
            $channels = array( $channels );
        } else if ( count( $channels ) > 100 ) {
            throw new PusherException('An event can be triggered on a maximum of 100 channels in a single call.');
        }

        $data = $already_encoded ? $data : json_encode( $data );

        $body = array();
        $body[ 'name' ] = $event;
        $body[ 'data' ] = $data;
        $body[ 'channels' ] = $channels;

        if ($socket_id) {
            $body[ 'socket_id' ] = $socket_id;
        }

        return $this->post('events', array(), $body);
    }

    /**
     * Generates the signed parameters used in HTTP requests.
     *
     * @param $method string HTTP method
     * @param $path string path to the resource
     * @param $params array URL query params
     * @param $body string|null HTTP body
     * @return array a new set of params.
     **/
    private function signedParams($method, $path, $params, $body)
    {
        $params = array_merge($params, array(
            'auth_key' => $this->key_pair->key,
            'auth_version' => '1.0'
        );

        if (is_null($params['auth_timestamp'])) {
            $params['auth_timestamp'] = time();
        }

        if (!is_null($body)) {
            $params['body_md5'] = md5($body);
        }

        ksort($params);

        $string_to_sign = $method . "\n" . $path . "\n" . http_build_query($params);

        $params['auth_signature'] = $this->key_pair->sign($string_to_sign)
        return $params;
    }

    /**
     * Returns the User-Agent identifier of this client library. Used in
     * requestHeaders()
     *
     * @return string
     **/
    private function userAgent()
    {
        return 'PusherREST-PHP/' . pusher\VERSION .
            ' ' . $adapter->adapterName() .
            ' PHP/' . PHP_VERSION;
    }

    /**
     * @return string[]
     **/
    private function requestHeaders()
    {
        return array(
            'User-Agent: ' . $this->userAgent(),
            'Accept: application/json',
        );
    }
}

