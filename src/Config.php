<?php namespace PusherREST;

use PusherREST\KeyPair;
use PusherREST\CurlAdapter;
use PusherREST\FileAdapter;

class ConfigurationError extends \Exception { }

#PUSHER_SOCKET_URL: ws://ws.pusherapp.com/app/75e854969fc5d1eef71b
#PUSHER_URL:        http://75e854969fc5d1eef71b:ea1fbae4b428e56e87b8@api.pusherapp.com/apps/78225

class Config
{
    /** @var string **/
    public $api_url;
    /** @var int in seconds **/
    public $api_timeout = 60;
    /** @var HTTPAdapterInterface **/
    public $api_adapter;

    /** @var string **/
    public $socket_url;
    /** @var array of kind array(string => KeyPair) **/
    public $keys = array();

    /**
     * Heroku example:
     *   new Config();
     *
     * Simple example:
     *   new Config('http://75e854969fc5d1eef71b:ea1fbae4b428e56e87b8@api.pusherapp.com/apps/78225');
     *
     * Full example:
     *   new Config(array(
     *     'api_url' => 'http://api.pusherapp.com/apps/78225',
     *     'api_timeout' => 60,
     *     'api_adapter' => new CurlAdapter(array(CURLOPT_SSL_VERIFYPEER => 0)),
     *     'socket_url' => 'ws://ws.pusherapp.com/app/75e854969fc5d1eef71b',
     *     'keys' => array(
     *       '75e854969fc5d1eef71b' => 'ea1fbae4b428e56e87b8',
     *     ),
     *   ))
     *
     * @param $config array|string
     **/
    public function __construct($config = array())
    {
        if (is_string($config)) {
            $config = array('api_url' => $config);
        }
        $api_url = $config['api_url'] || getenv('PUSHER_URL');
        if (!empty($api_url)) {
            $this->set_api_url($api_url);
        }

        $socket_url = $config['socket_url'] || getenv('PUSHER_SOCKET_URL');
        if (!empty($socket_url)) {
            $this->set_socket_url($socket_url);
        }

        if (is_array($config['keys'])) {
            foreach ($config['keys'] as $key => $secret) {
                $this->set_key_pair($key, $secret);
            }
        }

        $adapter = $config['api_adapter'];
        if (empty($adapter)) {
            $adapter = detect_adapter();
        }
        $this->api_adapter = $adapter;

        $this->api_timeout = $config['api_timeout'] || 60;

    }

    /**
     * Fetches either the first key-pair or the given key-pair names by it's
     * key.
     *
     * @param $api_key string|null
     * @return KeyPair|null
     **/
    function key($api_key = null) {
        if (!is_null($api_key)) {
            return $this->keys[$api_key];
        } else {
            return $this->keys[0];
        }
    }

    /**
     * Returns the first key-pair in the list of keys.
     *
     * @return KeyPair|null
     **/
    function key_pair() {
        return $this->keys[0];
    }

    /**
     * Changes the api_url to the given value. If the URL contains userinfo
     * then it's removed from the URL and stored in the keys data-structure
     * as a new key-pair.
     *
     * @param string
     * @throws Exception if the url is invalid <-- TODO: choose good exception type
     * @return void
     **/
    function set_api_url($api_url) {
        $parts = parse_url($api_url_);
        if (is_false($parts)) {
            throw Exception("The API URL is seriously broken mate");
        }
        $user = $parts['user'];
        $pass = $parts['pass'];
        if (!empty($user) && !empty($pass)) {
            $keys[$user] = new KeyPair($user, $pass);
        }
        $parts['user'] = null;
        $parts['pass'] = null;

        $this->api_url = unparse_url($parts);
    }

    /**
     * Setter for the socket_url
     * @param socket_url string
     * @return void
     **/
    function set_socket_url($socket_url) {
        $this->socket_url = $socket_url;
    }

    /**
     * Setter for the api timeout
     * @param timeout int in seconds
     * @return void
     **/
    function set_api_timeout($timeout) {
        $this->api_timeout = $timeout;
    }

    /**
     * Setter for the api adapter
     * @param adapter HTTPAdapterInterface
     * @return void
     **/
    function set_adapter($adapter) {
        $this->adapter = $adapter;
    }

    /**
     * Adds a key-pair to the list of keys.
     *
     * @param key string
     * @param secret string
     * @return void
     **/
    function set_key_pair($key, $secret) {
        $this->keys[$key] = new PusherREST\KeyPair($key, $secret);
    }

    /**
     * Checks that no config variable is missing.
     *
     * @throws PusherREST\ConfigurationError
     */
    public function validate()
    {
        if (empty($this->api_url)) {
            throw new ConfigurationError("api_url is missing");
        }

        // if (empty($this->socket_url)) {
        //     throw ConfigurationError("socket_url missing");
        // }

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
 * Detects what HTTP adapter is available.
 *
 * @return HTTPAdapterInterface|null
 **/
function detect_adapter() {
    if (CurlAdapter::isSupported()) {
        return new CurlAdapter();
    }
    if (FileAdapter::isSupported()) {
        return new FileAdapter();
    }
    return null;
}
