<?php

namespace Pusher;

/**
 * Webhook data validator and extractor.
 *
 * On normal cases use $pusher->webhook($_SERVER); to instanciate this class.
 */
class WebHook
{
    /**
     * @var string
     */
    public $signature;

    /**
     * @var KeyPair
     */
    public $keyPair;

    /**
     * @var string
     */
    public $bodyFile;

    /**
     * @var array
     */
    public $data = array();

    /**
     * @param $config Config
     * @param $api_key string extracted from the X-Pusher-Key header
     * @param $signature string extracted from the X-Pusher-Signature header
     * @param $body_file string file to read the body from
     */
    public function __construct($config, $api_key, $signature, $body_file = 'php://input')
    {
        $this->signature = $signature;
        $this->keyPair = $config->keyPair($api_key);
        $this->bodyFile = $body_file;
    }

    /**
     * Checks the validity and signature of the data passed in the constructor.
     *
     * @return bool
     */
    public function valid()
    {
        if (empty($this->keyPair) || empty($this->signature)) {
            return false;
        }
        $this->readBody();

        return $this->keyPair->verify($this->signature, $this->body);
    }

    /**
     * Returns the events passed in the webhook body.
     *
     * @return array
     */
    public function events()
    {
        $this->readBody();

        return $this->data['events'];
    }

    /**
     * Returns the unix timestamp at which the webhook request was sent.
     *
     * @return int
     */
    public function timestamp()
    {
        $this->readBody();

        return (int) ($this->data['time_ms'] / 1000);
    }

    private function readBody()
    {
        if (!isset($this->body)) {
            $this->body = file_get_contents($this->bodyFile);
            $this->data = json_decode($this->body, true);
        }
    }
}
