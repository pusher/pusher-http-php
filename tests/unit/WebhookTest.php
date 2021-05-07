<?php

namespace unit;

use PHPUnit\Framework\TestCase;
use Pusher\Pusher;
use Pusher\PusherException;

class WebhookTest extends TestCase
{
    /**
     * @var string
     */
    private $auth_key;
    /**
     * @var Pusher
     */
    private $pusher;

    protected function setUp(): void
    {
        $this->auth_key = 'thisisaauthkey';
        $this->pusher = new Pusher($this->auth_key, 'thisisasecret', 1);
    }

    public function testValidWebhookSignature(): void
    {
        $signature = '40e0ad3b9aa49529322879e84de1aaaf18bde1efe839ca263d540cc865510d25';
        $body = '{"hello":"world"}';
        $headers = [
            'X-Pusher-Key'       => $this->auth_key,
            'X-Pusher-Signature' => $signature,
        ];

        $this->pusher->ensure_valid_signature($headers, $body);

        self::assertTrue(true);
    }

    public function testInvalidWebhookSignature(): void
    {
        $this->expectException(PusherException::class);

        $signature = 'potato';
        $body = '{"hello":"world"}';
        $headers = [
            'X-Pusher-Key'       => $this->auth_key,
            'X-Pusher-Signature' => $signature,
        ];
        $this->pusher->ensure_valid_signature($headers, $body);
    }

    public function testDecodeWebhook(): void
    {
        $headers_json = '{"X-Pusher-Key":"' . $this->auth_key . '","X-Pusher-Signature":"a19cab2af3ca1029257570395e78d5d675e9e700ca676d18a375a7083178df1c"}';
        $body = '{"time_ms":1530710011901,"events":[{"name":"client_event","channel":"private-my-channel","event":"client-event","data":"Unencrypted","socket_id":"240621.35780774"}]}';
        $headers = json_decode($headers_json, true, 512, JSON_THROW_ON_ERROR);

        $decodedWebhook = $this->pusher->webhook($headers, $body);
        self::assertEquals(1530710011901, $decodedWebhook->get_time_ms());
        self::assertCount(1, $decodedWebhook->get_events());
    }
}
