<?php

namespace PusherREST;

/**
 * A HTTP client that uses the file_get_contents method. This adapter is
 * useful on Google AppEngine or other environments where the cUrl extension
 * is not available.
 */
class FileAdapter implements HTTPAdapter {

    /**
     * @see HTTPAdapter
     */
    public static function isSupported() {
        return ini_get('allow_url_fopen');
    }

    public $options;

    /**
     * @param $options array context options to be merged in during request.
     */
    public function __construct($options = array()) {
        $this->options = $options;
    }

    /**
     * @see HTTPAdapter
     */
    public function request($method, $url, $headers, $body, $timeout) {
        var_dump($url);
        $options = [
            'http' => [
                'method' => $method,
                'header' => join("\r\n", $headers),
                'ignore_errors' => true,
                'follow_location' => 0,
                'timeout' => $timeout,
            ],
            'ssl' => [
                'verify_peer' => true,
                //'cafile' => '/path/to/cafile.pem',
                //'CN_match' => 'example.com',
                'ciphers' => 'HIGH:!SSLv2:!SSLv3',
                'disable_compression' => true,
            ],
        ];
        $options = array_merge_recursive($this->options, $options);
        if (!is_null($body)) {
            $options['http']['content'] = $body;
        }
        $context = stream_context_create($options);
        $body = file_get_contents($url, false, $context);
        $headers = $http_response_header; // magic variable
        $response_line = array_shift($headers);

        $status = explode(' ', $response_line)[1];

        return array('status' => $status, 'body' => $body, 'headers' => $headers);
    }

    public function adapterName() {
        return 'file/0.0.0';
    }

}
