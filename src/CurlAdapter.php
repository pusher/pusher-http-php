<?php

namespace PusherREST;

use PusherREST\HTTPAdapter;

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

    public $opts;

    /**
     * @param $opts array options to be merged in during request.
     */
    public function __construct($opts = array()) {
        $this->opts = $opts;
    }

    /**
     * @see HTTPAdapter
     */
    public function request($method, $url, $headers, $body, $timeout) {
        # Set cURL opts and execute request
        $ch = curl_init($url);
        if (!$ch) {
            throw new AdapterError('curl_init: Could not initialise cURL');
        }

        $opts = array_replace($this->opts, array(
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
        ));

        if (!is_null($body)) {
            // FIXME: Only POST, how to set the method ?
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = $body;
        }

        foreach ($opts as $key => $value) {
            if (!curl_setopt($ch, $key, $value)) {
                throw new AdapterError("curl_setopt_array: Invalid cURL option $key => $value");
            }
        }

        $body = curl_exec($ch);

        if (curl_errno($ch) > 0) {
            throw new AdapterError("curl: " . curl_error($ch));
        }

        $info = curl_getinfo($ch);

        // TODO: Headers
        $response = array(
            'status' => $info['http_code'],
            'body' => $body,
        );

        curl_close($ch);

        return $response;
    }

    public function adapterName() {
        return 'curl/' . curl_version()['version'];
    }

}
