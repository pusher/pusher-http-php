<?php

class webhookTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->auth_key = 'thisisaauthkey';
        $this->pusher = new Pusher\Pusher($this->auth_key, 'thisisasecret', 1, true);
    }

    public function testValidWebhookSignature()
    {
        $signature = '40e0ad3b9aa49529322879e84de1aaaf18bde1efe839ca263d540cc865510d25';
        $body = '{"hello":"world"}';
        $headers = array(
            'X-Pusher-Key'       => $this->auth_key,
            'X-Pusher-Signature' => $signature,
        );

        $this->pusher->ensure_valid_signature($headers, $body);
    }

    /**
     * @expectedException \Pusher\PusherException
     */
    public function testInvalidWebhookSignature()
    {
        $signature = 'potato';
        $body = '{"hello":"world"}';
        $headers = array(
            'X-Pusher-Key'       => $this->auth_key,
            'X-Pusher-Signature' => $signature,
        );
        $wrong_signature = $this->pusher->ensure_valid_signature($headers, $body);
    }
}
