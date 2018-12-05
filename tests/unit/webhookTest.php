<?php

class webhookTest extends PHPUnit\Framework\TestCase
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

    public function testDecodeWebhook()
    {
        $headers_json = '{"X-Pusher-Key":"'.$this->auth_key.'","X-Pusher-Signature":"a19cab2af3ca1029257570395e78d5d675e9e700ca676d18a375a7083178df1c"}';
        $body = '{"time_ms":1530710011901,"events":[{"name":"client_event","channel":"private-my-channel","event":"client-event","data":"Unencrypted","socket_id":"240621.35780774"}]}';
        $headers = json_decode($headers_json, true);

        $decodedWebhook = $this->pusher->webhook($headers, $body);
        $this->assertEquals($decodedWebhook->get_time_ms(), 1530710011901);
        $this->assertEquals(count($decodedWebhook->get_events()), 1);
    }
}
