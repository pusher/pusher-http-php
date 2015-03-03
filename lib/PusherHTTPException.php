<?php

require_once('PusherException.php');

class PusherHTTPException extends PusherException {
  public $status;
  public $body;
  
  public function __construct($response) {
    $this->status = $response['status'];
    $this->body = $response['body'];
  }
}
