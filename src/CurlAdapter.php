<?php namespace PusherREST;

use PusherREST\HTTPAdapterInterface;

/**
 * A HTTP client that uses the venerable cURL library
 **/
class CurlAdapter implements HTTPAdapterInterface
{
    /**
     * @see HTTPAdapterInterface
     **/
    public static function isSupported()
    {
        //return false;
        return extension_loaded('curl');
    }

    public $opts;

    /**
     * @param $opts array options to be merged in during request.
     **/
    public function __construct($opts = array())
    {
        $this->opts = $opts;
    }

    /**
     * @see HTTPAdapterInterface
     **/
    public function request($method, $url, $headers, $body, $timeout)
    {
        # Set cURL opts and execute request
        $ch = curl_init();
        if (!$ch) {
            throw new AdapterError('curl_init: Could not initialise cURL');
        }

        $opts = array_merge($this->opts, array(
            CURLOPT_URL => $url,
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

        if (!curl_setopt_array($ch, $opts)) {
            throw new AdapterError("curl_setopt_array: Invalid cURL option");
        }

        $body = curl_exec( $ch );
        //curl_exec($ch);
        $info = curl_getinfo($ch);
        $info['curl_result'] = curl_errno($ch);
        if ($info['curl_result']) {
            $info['curl_error'] = curl_error($ch);
        }

        if ($body === false) {
            // fail
        }

        $response = array(
            'status' => $info,
            'body' => $body,
        );
        var_dump($response);

        curl_close( $ch );

        return $response;
    }

    public function adapterName()
    {
        return 'curl/' . curl_version()['version'];
    }
}
