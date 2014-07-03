<?php

namespace pusher;

/**
 *
 */
class WebHook {

    public $signature;
    public $keyPair;
    public $bodyFile;
    public $data = array();

    /**
     * @param $request array
     * @param $body_file string
     * @param $config pusher\Config
     */
    public function __construct($request, $body_file, $config) {
        $api_key = $request['HTTP_X_PUSHER_KEY'];
        $this->signature = $request['HTTP_X_PUSHER_SIGNATURE'];
        $this->keyPair = $config->keyPair($api_key);
        $this->bodyFile = $body_file;
    }

    /**
     *
     * @return boolean
     */
    public function valid() {
        if (empty($this->keyPair) || empty($this->signature)) {
            return false;
        }
        $this->readBody();
        return $this->key_pair->verify($this->body);
    }

    /**
     *
     * @return array
     */
    public function events() {
        $this->readBody();
        return $this->data['events'];
    }

    /**
     * @return int
     */
    public function timestamp() {
        $this->readBody();
        return (int) ($this->data['time_ms'] / 1000);
    }

    private function readBody() {
        if (!isset($this->body)) {
            $this->body = file_get_contents($this->bodyFile);
            $this->data = json_decode($this->body);
        }
    }

}
