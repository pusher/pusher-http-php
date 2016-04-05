<?php

namespace Pusher\Http;

use Pusher\Exception\HttpException;
use Pusher\Version;

/**
 * Simple HTTP client that encode and decodes request/responses using the
 * pusher conventions.
 */
class Client
{
    /**
     * @var string
     */
    public $baseUrl;

    /**
     * @var Adapter
     */
    public $adapter;

    /**
     * @var int in seconds
     */
    public $timeout;

    /**
     * @var string|null
     */
    public $proxyUrl;

    /**
     * @var KeyPair
     */
    public $keyPair;

    /**
     * @param $config Config
     */
    public function __construct($config)
    {
        $this->baseUrl = $config->baseUrl;
        $this->adapter = $config->adapter;
        $this->timeout = $config->timeout;
        $this->proxyUrl = $config->proxyUrl;
        $this->keyPair = $config->firstKeyPair();
    }

    /**
     * @param $rel_path string
     * @param $params array
     */
    public function get($rel_path, $params)
    {
        return $this->request('GET', $rel_path, $params);
    }

    /**
     * @param $rel_path string
     * @param $body string
     */
    public function post($rel_path, $body)
    {
        return $this->request('POST', $rel_path, array(), $body);
    }

    /**
     * @param $method string
     * @param $rel_path string
     * @param $params array
     * @param $body array|null
     *
     * @throws Pusher\Exception\HttpException on invalid responses
     *
     * @return mixed
     */
    public function request($method, $rel_path, $params = array(), $body = null)
    {
        $method = strtoupper($method);
        if (!is_null($body)) {
            $body = json_encode($body);
        }
        $base_path = parse_url($this->baseUrl, PHP_URL_PATH);
        $full_path = $this->pathJoin($base_path, $rel_path);
        $params = $this->signedParams($method, $full_path, $params, $body);
        $full_url = $this->pathJoin($this->baseUrl, $rel_path).'?'.http_build_query($params);

        $response = $this->adapter->request(
            $method,
            $full_url,
            $this->requestHeaders(!is_null($body)),
            $body,
            $this->timeout,
            $this->proxyUrl
        );

        switch ($response['status']) {
            case 200:
                return json_decode($response['body']);
            case 202:
                return true;
            case 400:
                throw new HttpException('Bad request', $response);
            case 401:
                throw new HttpException('Authentication error', $response);
            case 404:
                throw new HttpException('Not Found', $response);
            case 407:
                throw new HttpException('Proxy Authentication Required', $response);
            default:
                throw new HttpException('Unknown error', $response);
        }
    }

    /**
     * Returns the User-Agent identifier of this client library. Used in
     * requestHeaders().
     *
     * @return string
     */
    private function userAgent()
    {
        return 'pusher-http-php/'.Version::VERSION.
                ' '.$this->adapter->adapterId().
                ' PHP/'.PHP_VERSION;
    }

    /**
     * Returns HTTP headers used in all the requests.
     *
     * @param $has_body boolean
     *
     * @return string[]
     */
    private function requestHeaders($has_body)
    {
        $headers = array(
            'User-Agent: '.$this->userAgent(),
            'Accept: application/json',
            'Connection: keep-alive',
        );
        if ($has_body) {
            $headers[] = 'Content-Type: application/json';
        }

        return $headers;
    }

    /**
     * Generates the signed parameters used in HTTP requests.
     *
     * @param $method string HTTP method
     * @param $path string path to the resource
     * @param $params array array(string => string) URL query params
     * @param $body string|null HTTP body
     *
     * @return array a new set of params.
     */
    private function signedParams($method, $path, $params, $body)
    {
        $method = strtoupper($method);

        $params = array_replace($params, array(
            'auth_key' => $this->keyPair->key,
            'auth_version' => '1.0',
        ));

        if (!isset($params['auth_timestamp'])) {
            $params['auth_timestamp'] = time();
        }

        if (!is_null($body)) {
            $params['body_md5'] = md5($body);
        }

        // All params need to be lowercase
        $params = array_change_key_case($params);
        $params = array_filter($params);

        ksort($params);
        $query = urldecode(http_build_query($params));

        $string_to_sign = implode("\n", array($method, $path, $query));

        $params['auth_signature'] = $this->keyPair->sign($string_to_sign);

        return $params;
    }

    /**
     * Util to join two strings a/b.
     *
     * @param $a string
     * @param $b string
     *
     * @return string
     */
    private function pathJoin($a, $b)
    {
        return rtrim($a, '/').'/'.ltrim($b, '/');
    }
}
