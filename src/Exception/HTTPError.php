<?php

namespace pusher\Exception;

class HTTPError extends Exception {

    /**
     * @var array contains the http response as an array('status' => int, 'body' => string)
     */
    public $response;

    public function __construct($reason, $response) {
        $this->response = $response;
        parent::__construct($reason, $response['status']);
    }

}
