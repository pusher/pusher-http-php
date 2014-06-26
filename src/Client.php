<?php namespace PusherREST;

use PusherREST\VERSION;

class Client
{
    /** @var string **/
    public $baseUrl;

    /** @var HTTPAdapterInterface **/
    public $adapter;

    /** @var int in seconds **/
    public $timeout;

    /** @var PusherREST\KeyPair **/
    public $keyPair;


    /**
     * @param $config PusherREST\Config
     **/
    public function __construct($config)
    {
        $this->baseUrl = $config->apiUrl;
        $this->adapter = $config->apiAdapter;
        $this->timeout = $config->apiTimeout;
        $this->keyPair = $config->firstKeyPair();
    }

    /**
     * @param $method string
     * @param $rel_path string
     * @param $params array
     * @param $body array|null
     **/
    public function request($method, $rel_path, $params = array(), $body = null)
    {
        $method = strtoupper($method);
        if (!is_null($body)) {
            $body = json_encode($body);
        }
        $base_path = parse_url($this->baseUrl, PHP_URL_PATH);
        $path = path_join($base_path, $rel_path);
        $params = $this->keyPair->signedParams($method, $path, $params, $body);
        $response = $this->adapter->request(
            $method,
            path_join($this->baseUrl, $rel_path) . '?' . http_build_query($params),
            $this->requestHeaders(!is_null($body)),
            $body,
            $this->timeout);
        var_dump($response);
        // TODO: handle bad requests
        return json_decode($response);
    }

    public function get($rel_path, $params)
    {
        return $this->request('GET', $rel_path, $params);
    }

    public function post($rel_path, $body)
    {
        return $this->request('POST', $rel_path, array(), $body);
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

        return $this->post('events', $body);
    }

    /**
     * Returns the User-Agent identifier of this client library. Used in
     * requestHeaders()
     *
     * FIXME: VERSION constant is not replaced by it's value
     *
     * @return string
     **/
    private function userAgent()
    {
        return 'PusherREST-PHP/' . VERSION .
            ' ' . $this->adapter->adapterName() .
            ' PHP/' . PHP_VERSION;
    }

    /**
     * @return string[]
     **/
    private function requestHeaders($has_body)
    {
        $headers = array(
            'User-Agent: ' . $this->userAgent(),
            'Accept: application/json',
        );
        if ($has_body) {
            $headers[] = 'Content-Type: application/json';
        }
        return $headers;
    }
}

function path_join($a, $b)
{
    if ($a[-1] != "/") {
        $a .= "/";
    }
    return $a . $b;
}
