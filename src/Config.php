<?php namespace PusherREST;

/**
 * export PUSHER_URL=http://75e854969fc5d1eef71b:ea1fbae4b428e56e87b8@api.pusherapp.com/apps/78225
 * export PUSHER_SOCKET_URL=ws://ws.pusherapp.com/app/75e854969fc5d1eef71b
 **/
class Config
{
    /** @var string **/
    public $apiUrl;
    /** @var int in seconds **/
    public $apiTimeout = 60;
    /** @var HTTPAdapterInterface **/
    public $apiAdapter;

    /** @var string **/
    public $socketUrl;
    /** @var array of kind array(string => KeyPair) **/
    private $keys = array();

    /**
     * Returns an instance of the first adapter that is supported in the current
     * PHP runtime.
     *
     * @todo Make the resolution extensible
     * @return CurlAdapter|FileAdapter|null
     **/
    public static function detectAdapter() {
        if (CurlAdapter::isSupported()) {
            return new CurlAdapter();
        }
        if (FileAdapter::isSupported()) {
            return new FileAdapter();
        }
        return null;
    }


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

        $api_url = $config['api_url'];
        if (empty($api_url)) {
            $api_url = getenv('PUSHER_URL');
        }

        $this->setApiUrl($api_url);

        $this->socketUrl = $config['socket_url'];
        if (empty($this->socketUrl)) {
            $this->socketUrl = getenv('PUSHER_SOCKET_URL');
        }

        if (is_array($config['keys'])) {
            foreach ($config['keys'] as $key => $secret) {
                $this->setKeyPair($key, $secret);
            }
        }

        $adapter = $config['api_adapter'];
        if (empty($adapter)) {
            $adapter = Config::detectAdapter();
        }
        $this->apiAdapter = $adapter;

        $this->apiTimeout = $config['api_timeout'] || 60;
    }

    /**
     * Changes the api_url to the given value. If the URL contains userinfo
     * then it's removed from the URL and stored in the keys data-structure
     * as a new key-pair.
     *
     * If PHP's parse_url is not able to parse the URL the function doesn't
     * change the value of $this->apiUrl and returns false.
     *
     * @param string
     * @return boolean
     **/
    function setApiUrl($api_url) {
        $parts = parse_url($api_url);
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

        $this->apiUrl = unparse_url($parts);
        return true;
    }

    /**
     * Fetches either the first key-pair or the given key-pair names by it's
     * key.
     *
     * @param $api_key string
     * @return KeyPair|null
     **/
    function getKeyPair($api_key) {
        return $this->keys[$api_key];
    }

    /**
     * Returns the first key-pair in the list of keys.
     *
     * @return KeyPair|null
     **/
    function firstKeyPair() {
        return reset($this->keys);
    }

    /**
     * Adds a key-pair to the list of keys.
     *
     * @param key string
     * @param secret string
     * @return void
     **/
    function setKeyPair($key, $secret) {
        $this->keys[$key] = new KeyPair($key, $secret);
    }

    /**
     * Checks that no config variable is missing.
     *
     * @throws PusherREST\ConfigurationError
     */
    public function validate()
    {
        if (empty($this->apiUrl)) {
            throw new ConfigurationError("api_url is missing");
        }

        // if (empty($this->socket_url)) {
        //     throw ConfigurationError("socket_url missing");
        // }

        if (empty($this->keys)) {
            throw new ConfigurationError("keys are missing");
        }

        if (empty($this->apiAdapter)) {
            throw new ConfigurationError("adapter is missing");
        }

        if (empty($this->apiTimeout)) {
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
 **/
function unparse_url($parsed_url) {
  $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
  $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
  $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
  $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
  $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
  $pass     = ($user || $pass) ? "$pass@" : '';
  $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
  $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
  $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
  return "$scheme$user$pass$host$port$path$query$fragment";
}
