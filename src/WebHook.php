<?php

namespace pusher;

/**
 *
 */
class WebHook {

    public $signature;
    public $key_pair;
    public $body;
    public $data = array();

    /**
     *
     */
    public function __construct($request, $config) {
        $api_key = $request['HTTP_X_PUSHER_KEY'];
        $this->signature = $request['HTTP_X_PUSHER_SIGNATURE'];
        $this->key_pair = $config->keyPair($api_key);
        if (!empty($this->signature) && !empty($this->key_pair)) {
            $this->body = file_get_contents('php://input');
            if (!empty($this->body)) {
                $this->data = json_decode($this->body, true);
            }
        }
    }

    /**
     *
     * @return boolean
     */
    public function valid() {
        if (empty($this->key_pair) || empty($this->signature) || empty($this->body)) {
            return false;
        }
        return $this->key_pair->verify($this->body);
    }

    /**
     *
     * @return array
     */
    public function events() {
        return $this->data['events'];
    }

    /**
     * @return int
     */
    public function timestamp() {
        return (int) ($this->data['time_ms'] / 1000);
    }

}
