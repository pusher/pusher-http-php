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
        return false;
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
        if ( $ch === false ) {
            throw new Exception('Could not initialise cURL!');
        }

        try {
            $opts = array_merge(array(
                CURLOPT_URL => $url,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            ), $this->opts);

            if (!is_null($body)) {
                // FIXME: Only POST, how to set the method ?
                $opts[CURLOPT_POST] = 1;
                $opts[CURLOPT_POSTFIELDS] = $body;
            }

            curl_setopt_array($ch, $opts);

            $body = curl_exec( $ch );

            if ($body === false) {
                // fail
            }

            var_dump(curl_getinfo($ch));

            $response = array(
                'status' => curl_getinfo( $ch, CURLINFO_HTTP_CODE ),
                'body' => $body,
            );
            var_dump($response);
        } catch(Exception $e) {
            curl_close( $ch );
            throw $e;
        }

        curl_close( $ch );

        return $response;
    }

    public function adapterName()
    {
        return 'curl/' . curl_version()['version'];
    }
}
