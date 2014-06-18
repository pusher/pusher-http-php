<?php namespace pusher;

use PusherREST\HTTPAdapterInterface;

/**
 * A HTTP client that uses the file_get_contents method. This adapter is
 * useful on Google AppEngine where the cUrl extension is not available.
 **/
class FileAdapter implements HTTPAdapterInterface
{
    /**
     * @see HTTPAdapterInterface
     **/
    public static function isSupported()
    {
        return ini_get('allow_url_fopen');
    }

    public $opts;

    /**
     * @param $opts array options to be merged in during request.
     **/
    public function __construct($opts = array())
    {
        $this->opts = opts;
    }

    /**
     * @see HTTPAdapterInterface
     **/
    public function request($method, $url, $headers, $body, $timeout)
    {
        $context = [
            'http' => [
                'method' => $method,
                'header' => join("\r\n", $headers) . "\r\n",
                'content' => $data
            ]
        ];
        $context = array_merge_recursive($this->opts, $context);
        if (!is_null($body)) {
            $context['http']['content'] = $body;
        }
        $context = stream_context_create($context);
        $result = file_get_contents($url, false, $context);
        return $result;
    }

    public function adapterName()
    {
        return 'file/xxx';
    }
}
