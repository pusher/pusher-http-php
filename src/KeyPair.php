<?php

namespace Pusher;

/**
 * Container for the Pusher key:secret token.
 */
class KeyPair
{
    /** @var string */
    public $key;

    /** @var string */
    public $secret;

    /**
     * Used to delegate authorization. Generated signature can be transmitted
     * to socket libraries to connect to private and presence channels.
     *
     * @param $key string Pusher API key
     * @param $secret string Pusher API secret
     */
    public function __construct($key, $secret)
    {
        if (!in_array('sha256', hash_algos())) {
            throw new Exception(
                'SHA256 appears to be unsupported - make sure you have support for it, or upgrade your version of PHP.'
            );
        }
        $this->key = $key;
        $this->secret = $secret;
    }

    /**
     * Generates a signature of the given string.
     *
     * @param $string_to_sign string
     *
     * @return string hmac signature
     */
    public function sign($string_to_sign)
    {
        return hash_hmac('sha256', $string_to_sign, $this->secret, false);
    }

    /**
     * Used to verify a given signature against the secret key.
     *
     * @param $api_key string
     * @param $signature string signature to verify
     * @param $string_to_sign string content to verify
     *
     * @return bool true if the signature matches
     */
    public function verify($signature, $string_to_sign)
    {
        $s2 = $this->sign($string_to_sign);

        return $this->constantCompare($signature, $s2);
    }

    /**
     * Generates a signature for a socket_id and channel pair. Presence
     * channels might also have custom data attached to them.
     *
     * @param $socket_id string Id of the socket to authorize
     * @param $channel_name string Name of the channel to authorize
     * @param $channel_data null|string Additional data to authorize
     *
     * @return string hmac sha256 signature
     */
    public function authenticate($socket_id, $channel_name, $channel_data = null)
    {
        $string_to_sign = $socket_id.':'.$channel_name;

        if (is_string($channel_data)) {
            $string_to_sign .= ':'.$channel_data;
        }

        return $this->sign($string_to_sign);
    }

    /**
     * Compares string a and b in constant time. Used to avoid side-channel
     * timing attacks.
     *
     * @param a string
     * @param b string
     *
     * @return bool true if the two strings are equal
     */
    private function constantCompare($a, $b)
    {
        if (strlen($a) !== strlen($b)) {
            return false;
        }
        $result = 0;
        $len = strlen($a);
        for ($i = 0; $i < $len; $i++) {
            $result |= ord($a[$i]) ^ ord($b[$i]);
        }

        return $result === 0;
    }
}
