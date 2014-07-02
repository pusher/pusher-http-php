<?php

namespace pusher;

use pusher\KeyPair;
use pusher\Exception\ConfigurationError;

/**
 *
 * Heroku example:
 *   new Config(getenv('PUSHER_URL'));
 *
 * Simple example:
 *   new Config('http://75e854969fc5d1eef71b:ea1fbae4b428e56e87b8@api.pusherapp.com/apps/78225');
 *
 * Full example:
 *   new Config(array(
 *     'base_url' => 'http://api.pusherapp.com/apps/78225',
 *     'timeout' => 5,
 *     'proxy_url' => 'http://localhost:8080',
 *     'adapter' => new CurlAdapter(array(CURLOPT_SSL_VERIFYPEER => 0)),
 *     'keys' => array(
 *       '75e854969fc5d1eef71b' => 'ea1fbae4b428e56e87b8',
 *     ),
 *   ))
 *
 */
class Config {

    /** @var string */
    public $baseUrl;

    /** @var int in seconds */
    public $timeout = 5;

    /** @var HTTPAdapter */
    public $adapter;

    /** @var array of kind array(string => KeyPair) */
    private $keys = array();

    /**
     * Returns an instance of the first adapter that is supported in the current
     * PHP runtime.
     *
     * @todo Make the resolution extensible
     * @param $adapter_options array array('curl_adapter' => array(), 'file_adapter' => array())
     * @return CurlAdapter|FileAdapter|null
     */
    public static function detectAdapter($adapter_options) {
        if (CurlAdapter::isSupported()) {
            return new CurlAdapter($adapter_options['curl_adapter']);
        }
        if (FileAdapter::isSupported()) {
            return new FileAdapter($adapter_options['file_adapter']);
        }
        return null;
    }

    /**
     * @param $config array|string
     */
    public function __construct($config = array()) {
        if (is_string($config)) {
            $config = array('base_url' => $config);
        }

        $url = $config['base_url'];
        if (!empty($url)) {
            $this->setBaseUrl($url);
        }

        if (is_array($config['keys'])) {
            foreach ($config['keys'] as $key => $secret) {
                $this->setKeyPair($key, $secret);
            }
        }

        $this->proxy_url = $config['proxy_url'];

        $adapter = $config['adapter'];
        if (empty($adapter)) {
            $adapter = Config::detectAdapter($config);
        }
        $this->adapter = $adapter;

        $timeout = $config['timeout'];
        if (is_int($timeout)) {
            $this->timeout = $timeout;
        }
    }

    /**
     * Changes the base_url to the given value. If the URL contains userinfo
     * then it's removed from the URL and stored in the keys data-structure
     * as a new key-pair.
     *
     * If PHP's parse_url is not able to parse the URL the function doesn't
     * change the value of $this->baseUrl and returns false.
     *
     * @param string
     * @return boolean
     */
    function setBaseUrl($base_url) {
        $parts = parse_url($base_url);
        if ($parts === false) {
            return false;
        }

        // $parts['host'] = 'localhost';
        // $parts['port'] = '1234';

        $user = $parts['user'];
        $pass = $parts['pass'];
        if (!empty($user) && !empty($pass)) {
            $this->setKeyPair($user, $pass);
        }
        unset($parts['user']);
        unset($parts['pass']);

        $this->baseUrl = unparse_url($parts);
        return true;
    }

    /**
     * Fetches either the first key-pair or the given key-pair names by it's
     * key.
     *
     * @param $api_key string
     * @return KeyPair|null
     */
    function keyPair($api_key) {
        return $this->keys[$api_key];
    }

    /**
     * Returns the first key-pair in the list of keys.
     *
     * @return KeyPair|null
     */
    function firstKeyPair() {
        return reset($this->keys);
    }

    /**
     * Adds a key-pair to the list of keys.
     *
     * @param key string
     * @param secret string
     * @return void
     */
    function setKeyPair($key, $secret) {
        $this->keys[$key] = new KeyPair($key, $secret);
    }

    /**
     * Checks that no config variable is missing.
     *
     * @throws pusher\ConfigurationError
     */
    public function validate() {
        if (empty($this->baseUrl)) {
            throw new ConfigurationError("baseUrl is missing");
        }

        if (empty($this->keys)) {
            throw new ConfigurationError("keys are missing");
        }

        if (empty($this->adapter)) {
            throw new ConfigurationError("adapter is missing");
        }

        if (empty($this->timeout)) {
            throw new ConfigurationError("timeout is not set");
        }
    }

}

/**
 * Utility function to recompose an array returns from parse_url into an URL.
 *
 * @see http://uk3.php.net/manual/en/function.parse-url.php#106731
 * @param $parsed_url array
 * @return string
 */
function unparse_url($parsed_url) {
    $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
    $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
    $port = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
    $user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
    $pass = isset($parsed_url['pass']) ? ':' . $parsed_url['pass'] : '';
    $pass = ($user || $pass) ? "$pass@" : '';
    $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
    $query = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
    $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
    return "$scheme$user$pass$host$port$path$query$fragment";
}
