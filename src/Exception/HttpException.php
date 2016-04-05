<?php

namespace Pusher\Exception;

class HttpException extends Exception
{
    /**
     * @var array contains the http response as an array('status' => int, 'body' => string)
     */
    public $response;

    /**
     * @param $reason string HTTP reason
     * @param $response array HTTP response returned by the adapter
     */
    public function __construct($reason, $response)
    {
        $this->response = $response;
        parent::__construct($reason, $response['status']);
    }
}
