<?php

namespace Pusher\Http;

/**
 * Adapter interface used to abstract the HTTP client.
 */
interface Adapter
{
    /**
     * Used to determine if this adapter is available. Depending on the PHP
     * installation some extensions might not be enabled.
     *
     * @return bool true if supported
     */
    public static function isSupported();

    /**
     * @param $adapter_options array options specific for the adapter
     */
    public function __construct($adapter_options);

    /**
     * Performs a single HTTP request from the given arguments.
     *
     * @param $method string
     * @param $url string
     * @param $headers string[]
     * @param $body string|null
     * @param $timeout int
     * @param $proxy_url string|null
     *
     * @return array with array('status' => int, 'body' => string)
     */
    public function request($method, $url, $headers, $body, $timeout, $proxy_url);

    /**
     * Returns the name of the adapter which is added in the User-Agent header
     * The format should be name/version. Eg: "curl/1.3.0".
     *
     * @return string
     */
    public function adapterId();
}
