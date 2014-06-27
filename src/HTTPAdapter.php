<?php

namespace PusherREST;

/**
 * Adapter interface used to abstract the HTTP client.
 */
interface HTTPAdapter {

    /**
     * Used to determine if this adapter is available. Depending on the PHP
     * installation some extensions might now be enabled.
     *
     * @return boolean true if supported
     */
    public static function isSupported();

    /**
     * Performs a single HTTP request from the given arguments.
     *
     * @param $method string
     * @param $uri string
     * @param $headers string[]
     * @param $body string|null
     * @param $timeout int in seconds
     * @return array with array('status' => int, 'body' => string)
     */
    public function request($method, $url, $headers, $body, $timeout);

    /**
     * Returns the name of the adapter which is added in the User-Agent header
     * The format should be name/version. Eg: "curl/1.3.0"
     *
     * @return string
     */
    public function adapterName();
}
