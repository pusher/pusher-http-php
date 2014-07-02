<?php

namespace pusher;

use pusher\HTTPAdapter;
use pusher\Exception\AdapterError;

/**
 * A HTTP client that uses the venerable cURL library
 */
class CurlAdapter implements HTTPAdapter {

    /**
     * @see HTTPAdapter
     */
    public static function isSupported() {
        return extension_loaded('curl');
    }

    public $options = array();

    /**
     * @param $options array options to be merged in during request.
     */
    public function __construct($options = array()) {
        if (is_array($options)) {
            $this->options = $options;
        }
    }

    /**
     * @see HTTPAdapter
     * @throws pusher\Exception\AdapterError
     */
    public function request($method, $url, $headers, $body, $timeout, $proxy_url) {
        # Set cURL opts and execute request
        $ch = curl_init($url);
        if (!$ch) {
            throw new AdapterError('curl_init: Could not initialise cURL');
        }

        $options = array_replace($this->options, array(
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
        ));

        if (!is_null($body)) {
            // FIXME: Only POST, how to set the method ?
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = $body;
        }

        if (!empty($proxy_url)) {
            $options[CURLOPT_PROXY] = $proxy_url;
        }

        foreach ($options as $key => $value) {
            if (!curl_setopt($ch, $key, $value)) {
                throw new AdapterError("curl_setopt_array: Invalid cURL option $key => $value");
            }
        }

        $body = curl_exec($ch);

        if (curl_errno($ch) > 0) {
            throw new AdapterError("curl: " . curl_error($ch));
        }

        $info = curl_getinfo($ch);

        // TODO: Headers ?
        $response = array(
            'status' => $info['http_code'],
            'body' => $body,
        );

        curl_close($ch);

        return $response;
    }

    public function adapterId() {
        $curl_version = curl_version();
        return 'curl/' . $curl_version['version'];
    }

}
