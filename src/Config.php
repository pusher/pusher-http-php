<?php

namespace Pusher;

use Pusher\Exception\ConfigurationException;
use Pusher\Http\CurlAdapter;
use Pusher\Http\FileAdapter;

/**
 * A configuration store used by \Pusher\Http\Client and \Pusher\Pusher.
 *
 * Heroku example:
 *   new Config(getenv('PUSHER_URL'));
 *
 * Simple example:
 *   new Config('http://75e854969fc5d1eef71b:ea1fbae4b428e56e87b8@api.pusherapp.com/apps/78225');
 *
 * Full example:
 *   new Config(array(
 *     'base_url' => 'http://api.pusherapp.com',
 *     'app_id' => '1234',
 *     'timeout' => 5,
 *     'proxy_url' => 'http://localhost:8080',
 *     'keys' => array(
 *       '75e854969fc5d1eef71b' => 'ea1fbae4b428e56e87b8',
 *     ),
 *   ))
 */
class Config
{
    /**
     * @var string
     */
    public $baseUrl;

    /**
     * @var int in seconds
     */
    public $timeout = 5;

    /**
     * @var string|null
     */
    public $proxyUrl;

    /**
     * @var \Pusher\Http\Adapter
     */
    public $adapter;

    /**
     * Default components of the baseUrl.
     *
     * @var array
     */
    protected $defaults = array(
        'scheme' => 'https',
        'host' => 'api.pusherapp.com',
    );

    /**
     * @var array of kind array(string => KeyPair)
     */
    private $keys = array();

    /**
     * Returns an instance of the first adapter that is supported in the current
     * PHP runtime.
     *
     * @todo Make the resolution extensible
     *
     * @param $adapterOptions array array('curl_adapter' => array(), 'file_adapter' => array())
     *
     * @return \Pusher\Http\CurlAdapter|\Pusher\Http\FileAdapter|null
     */
    public static function detectAdapter($adapterOptions)
    {
        if (CurlAdapter::isSupported()) {
            $opts = isset($adapterOptions['curl_adapter']) ? $adapterOptions['curl_adapter'] : array();

            return new CurlAdapter($opts);
        }
        if (FileAdapter::isSupported()) {
            $opts = isset($adapterOptions['file_adapter']) ? $adapterOptions['file_adapter'] : array();

            return new FileAdapter($opts);
        }
    }

    /**
     * @param $config array|string
     */
    public function __construct($config)
    {
        if (!is_string($config) && !is_array($config)) {
            throw new ConfigurationException('You have not provided a valid configuration.');
        }

        if (is_string($config)) {
            $config = array('base_url' => $config);
        }

        if (isset($config['encrypted'])) {
            $this->defaults['scheme'] = ($config['encrypted'] === true) ? 'https' : 'http';
        }

        if (isset($config['cluster'])) {
            $this->defaults['host'] = 'api-'.$config['cluster'].'.pusher.com';
        }

        if (!isset($config['base_url'])) {
            $config['base_url'] = $this->defaults['scheme'].'://'.$this->defaults['host'];
        }

        $appUrl = (isset($config['app_id'])) ?
            $config['base_url'].'/apps/'.$config['app_id'] : $config['base_url'];

        $this->setBaseUrl($appUrl);

        if (isset($config['keys']) && is_array($config['keys'])) {
            foreach ($config['keys'] as $key => $secret) {
                $this->setKeyPair($key, $secret);
            }
        }

        if (isset($config['proxy_url'])) {
            $this->proxyUrl = $config['proxy_url'];
        }

        $this->adapter = self::detectAdapter($config);

        if (isset($config['timeout']) && is_int($config['timeout'])) {
            $this->timeout = $config['timeout'];
        }

        $this->validate();
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
     *
     * @return bool
     */
    public function setBaseUrl($base_url)
    {
        $parts = parse_url($base_url);
        if ($parts === false) {
            return false;
        }

        if (!empty($parts['user']) && !empty($parts['pass'])) {
            $this->setKeyPair($parts['user'], $parts['pass']);

            unset($parts['user']);
            unset($parts['pass']);
        }

        $this->baseUrl = $this->unparseUrl($parts);

        return true;
    }

    /**
     * Fetches either the first key-pair or the given key-pair names by it's
     * key.
     *
     * @param $api_key string
     *
     * @return KeyPair|null
     */
    public function keyPair($api_key)
    {
        return $this->keys[$api_key];
    }

    /**
     * Returns the first key-pair in the list of keys.
     *
     * @return KeyPair|null
     */
    public function firstKeyPair()
    {
        return reset($this->keys);
    }

    /**
     * Adds a key-pair to the list of keys.
     *
     * @param key string
     * @param secret string
     *
     * @return void
     */
    public function setKeyPair($key, $secret)
    {
        $this->keys[$key] = new KeyPair($key, $secret);
    }

    /**
     * Checks that no config variable is missing.
     *
     * @throws Exception\ConfigurationException
     */
    public function validate()
    {
        if (empty($this->baseUrl)) {
            throw new ConfigurationException('baseUrl is missing.');
        }

        if (empty($this->keys)) {
            throw new ConfigurationException('keys are missing.');
        }

        if (empty($this->adapter)) {
            throw new ConfigurationException('adapter is missing.');
        }

        if (empty($this->timeout)) {
            throw new ConfigurationException('timeout is not set.');
        }
    }

    /**
     * Utility function to recompose an array returns from parse_url into an URL.
     *
     * @see http://uk3.php.net/manual/en/function.parse-url.php#106731
     *
     * @param $parsed_url array
     *
     * @return string
     */
    private function unparseUrl($parsed_url)
    {
        $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'].'://' : '';
        $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port = isset($parsed_url['port']) ? ':'.$parsed_url['port'] : '';
        $user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass = isset($parsed_url['pass']) ? ':'.$parsed_url['pass'] : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query = isset($parsed_url['query']) ? '?'.$parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#'.$parsed_url['fragment'] : '';

        return "$scheme$user$pass$host$port$path$query$fragment";
    }
}
