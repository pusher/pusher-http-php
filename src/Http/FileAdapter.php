<?php

namespace Pusher\Http;

use Pusher\Exception\AdapterException;

/**
 * A HTTP client that uses the file_get_contents method. This adapter is
 * useful on Google AppEngine or other environments where the cUrl extension
 * is not available.
 */
class FileAdapter implements Adapter
{
    /**
     * @see Adapter
     */
    public static function isSupported()
    {
        // for SSL support also check:
        //   extension_loaded('openssl') and in_array('https', $w)
        $w = stream_get_wrappers();

        return in_array('http', $w) && ini_get('allow_url_fopen');
    }

    public $options = array();

    /**
     * @param $options array options to be merged in during request.
     */
    public function __construct($options = array())
    {
        if (is_array($options)) {
            $this->options = $options;
        }
    }

    /**
     * @see Adapter
     */
    public function request($method, $url, $headers, $body, $timeout, $proxy_url)
    {
        $options = array(
            'http' => array(
                'method' => $method,
                'header' => implode("\r\n", $headers),
                'ignore_errors' => true,
                'follow_location' => 0,
                'timeout' => $timeout,
            ),
            'ssl' => array(
                'verify_peer' => true,
                'cafile' => __dir__.DIRECTORY_SEPARATOR.'cacert.pem',
                'ciphers' => 'HIGH:!SSLv2:!SSLv3',
                'disable_compression' => true,
            ),
        );
        $options = array_merge_recursive($this->options, $options);

        if (!is_null($body)) {
            $options['http']['content'] = $body;
        }

        if (!empty($proxy_url)) {
            $options['http']['proxy'] = $proxy_url;
            $options['http']['request_fulluri'] = true;
        }

        $context = stream_context_create($options);
        $body = @file_get_contents($url, false, $context);
        if ($body === false) {
            $error = error_get_last();
            throw new AdapterException($error['message'], $error['type']);
        }
        $headers = $http_response_header; // magic variable
        $response_line = array_shift($headers);

        $status = explode(' ', $response_line);

        return array('status' => $status[1], 'body' => $body, 'headers' => $headers);
    }

    public function adapterId()
    {
        return 'file/0.0.0';
    }
}
