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
        $base_path = parse_url($this->base_url, PHP_URL_PATH)['path'];
        $params = $this->signedParams($method, $base_path . $path, $params, $body);
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

