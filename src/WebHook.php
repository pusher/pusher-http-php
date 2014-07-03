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
     * @param $config pusher\Config
     * @param $api_key string extracted from the X-Pusher-Key header
     * @param $signature string extracted from the X-Pusher-Signature header
     * @param $body_file string file to read the body from
     */
    public function __construct($config, $api_key, $signature, $body_file = 'php://input') {
        $this->signature = $signature;
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
        return $this->keyPair->verify($this->signature, $this->body);
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
            $this->data = json_decode($this->body, true);
        }
    }

}
