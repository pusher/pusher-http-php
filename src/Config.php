<?php namespace PusherREST;

use PusherREST\KeyPair;
use PusherREST\CurlAdapter;
use PusherREST\FileAdapter;

class ConfigurationError extends \Exception { }

#PUSHER_SOCKET_URL: ws://ws.pusherapp.com/app/75e854969fc5d1eef71b
#PUSHER_URL:        http://75e854969fc5d1eef71b:ea1fbae4b428e56e87b8@api.pusherapp.com/apps/78225

class Config
{
    public $api_url;
    public $api_timeout = 60;
    public $api_adapter;

    public $socket_url;
    public $keys = array();


    /**
     * Example:
     *   new Config(array(
     *     'api_url' => 'http://api.pusherapp.com/apps/78225',
     *     'api_timeout' => 60,
     *     'api_adapter' => new CurlAdapter(array(CURLOPT_SSL_VERIFYPEER => 0)),
     *     'socket_url' => 'ws://ws.pusherapp.com/app/75e854969fc5d1eef71b',
     *     'keys' => array(
     *       '75e854969fc5d1eef71b' => 'ea1fbae4b428e56e87b8',
     *     ),
     *   ))
     **/
    public function __construct($opts = array())
    {
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
            $adapter = detectAdapter();
        }
        $this->api_adapter = $adapter;

        $this->api_timeout = $config['api_timeout'] || 60;

    }

    function key($api_key = null) {
        if (!is_null($api_key)) {
            return $this->keys[$api_key];
        } else {
            return $this->keys[0];
        }
    }

    function key_pair() {
        return $this->keys[0];
    }

    function set_api_url($api_url) {
        $parts = parse_url($api_url_);
        if (is_false($parts)) {
            throw Exception("The API URL is seriously broken mate");
        }
        $user, $pass = $parts['user'], $parts['pass'];
        if (!empty($user) && !empty($pass)) {
            $keys[$user] = new KeyPair($user, $pass);
        }
        $parts['user'] = null;
        $parts['pass'] = null;

        $this->api_url = unparse_url($parts);
    }

    function set_socket_url($socket_url) {
        $this->socket_url = $socket_url;
    }

    function set_timeout($timeout) {
        $this->timeout = $timeout;
    }

    function set_adapter($adapter) {
        $this->adapter = $adapter;
    }

    function set_key_pair($api_key, $api_secret) {
        $this->keys[$api_key] = new PusherREST\KeyPair($api_key, $api_secret);
    }

    /**
     * Checks that no config variable is missing.
     *
     * @throws PusherREST\ConfigurationError
     */
    public function validate()
    {
        if (empty($this->api_url)) {
            throw ConfigurationError("api_url missing");
        }

        // if (empty($this->socket_url)) {
        //     throw ConfigurationError("socket_url missing");
        // }

        if (empty($this->keys)) {
            throw ConfigurationError("keys missing");
        }

        if (empty($this->adapter)) {
            throw ConfigurationError("adapter missing");
        }

        if (empty($this->timeout)) {
            throw ConfigurationError("timeout not set");
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
