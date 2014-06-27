<?php

namespace PusherREST;

if (!in_array('sha256', hash_algos())) {
    throw new Exception('SHA256 appears to be unsupported - make sure you have support for it, or upgrade your version of PHP.');
}

class KeyPair {

    /** @var string * */
    public $key;

    /** @var string * */
    public $secret;

    /**
     * Used to delegate authorization. Generated signature can be transmitted
     * to socket libraries to connect to private and presence channels.
     *
     * @param $key string Pusher API key
     * @param $secret string Pusher API secret
     * */
    public function __construct($key, $secret) {
        $this->key = $key;
        $this->secret = $secret;
    }

    /**
     * Generates a signature for a socket_id and channel pair. Presence
     * channels might also have custom data attached to them.
     *
     * @param $socket_id string Id of the socket to authorize
     * @param $channl string Name of the channel to authorize
     * @param $user_data null|string Additional data to authorize
     * @return string hmac sha256 signature
     * */
    public function channelSignature($socket_id, $channel, $user_data = null) {
        $string_to_sign = $socket_id . ':' . $channel;

        if (is_string($user_data)) {
            $string_to_sign .= ':' . $user_data;
        }

        return $this->sign($string_to_sign);
    }

    /**
     * Generates the signed parameters used in HTTP requests.
     *
     * @param $method string HTTP method
     * @param $path string path to the resource
     * @param $params array array(string => string) URL query params
     * @param $body string|null HTTP body
     * @return array a new set of params.
     * */
    public function signedParams($method, $path, $params, $body) {
        $method = strtoupper($method);

        $params = array_merge($params, array(
            'auth_key' => $this->key,
            'auth_version' => '1.0',
        ));

        if (is_null($params['auth_timestamp'])) {
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

        $params['auth_signature'] = $this->sign($string_to_sign);
        return $params;
    }

    /**
     * Generates a signature of the given string
     *
     * @param $string_to_sign string
     * @return string hmac signature
     * */
    public function sign($string_to_sign) {
        return hash_hmac('sha256', $string_to_sign, $this->secret, false);
    }

    /**
     * Used to verify a given signature against the secret key.
     *
     * @param $api_key string
     * @param $signature string signature to verify
     * @param $string_to_sign string content to verify
     * @return bool true if the signature matches
     * */
    public function verify($signature, $string_to_sign) {
        return constant_compare($signature, $this->sign($string_to_sign));
    }

}

/**
 * Compares string a and b in constant time. Used to avoid side-channel
 * timing attacks.
 *
 * @param a string
 * @param b string
 * @return boolean true if the two strings are equal
 * */
function constant_compare($a, $b) {
    if (strlen($a) != strlen($b)) {
        return false;
    }
    $result = 0;
    $len = strlen($a);
    for ($i = 0; $i < $len; $i++) {
        $result |= ord($a[$i]) ^ ord($b[$i]);
    }
    return $result == 0;
}
