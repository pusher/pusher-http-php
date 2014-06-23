<?php namespace PusherREST;

/**
 * A HTTP client that uses the file_get_contents method. This adapter is
 * useful on Google AppEngine or other environments where the cUrl extension
 * is not available.
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

    public $options;

    /**
     * @param $options array context options to be merged in during request.
     **/
    public function __construct($options = array())
    {
        $this->options = $options;
    }

    /**
     * TODO: make sure of the $timeout var
     * @see HTTPAdapterInterface
     **/
    public function request($method, $url, $headers, $body, $timeout)
    {
        $options = [
            'http' => [
                'method' => $method,
                'header' => join("\r\n", $headers) . "\r\n"
            ]
        ];
        $options = array_merge_recursive($this->options, $options);
        if (!is_null($body)) {
            $options['http']['content'] = $body;
        }
        var_dump($options);
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return $result;
    }

    public function adapterName()
    {
        return 'file/0.0.0';
    }
}
