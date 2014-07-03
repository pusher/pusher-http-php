<?php

namespace pusher;

use pusher\HTTPAdapter;
use pusher\Exception\AdapterError;

/**
 * A HTTP client that uses the venerable cURL library. This adapter supports
 * Keep-Alive.
 */
class CurlAdapter implements HTTPAdapter {

    /**
     * @see HTTPAdapter
     */
    public static function isSupported() {
        return extension_loaded('curl');
    }

    public $options = array();
    private $ch;

    /**
     * @param $options array options to be merged in during request.
     * @throws pusher\Exception\AdapterError if curl_init() didn't work
     */
    public function __construct($options = array()) {
        if (is_array($options)) {
            $this->options = $options;
        }
        $this->ch = curl_init();
        if (!$this->ch) {
            throw new AdapterError('curl_init: Could not initialise cURL');
        }
    }

    public function __destruct() {
        curl_close($this->ch);
    }

    /**
     * @see HTTPAdapter
     * @throws pusher\Exception\AdapterError on invalid curl_setopt options
     */
    public function request($method, $url, $headers, $body, $timeout, $proxy_url) {

        $options = array_replace($this->options, array(
            CURLOPT_URL => $url,
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
            if (!curl_setopt($this->ch, $key, $value)) {
                throw new AdapterError("curl_setopt_array: Invalid cURL option $key => $value");
            }
        }

        $body = curl_exec($this->ch);

        if (curl_errno($this->ch) > 0) {
            throw new AdapterError("curl: " . curl_error($this->ch));
        }

        $info = curl_getinfo($this->ch);

        // TODO: Headers ?
        $response = array(
            'status' => $info['http_code'],
            'body' => $body,
        );

        return $response;
    }

    public function adapterId() {
        $curl_version = curl_version();
        return 'curl/' . $curl_version['version'];
    }

}
