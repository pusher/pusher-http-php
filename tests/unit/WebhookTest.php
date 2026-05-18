<?php

namespace unit;

use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
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

    public function testInvalidJsonBodyLogsWithArrayContext(): void
    {
        $logger = new class extends AbstractLogger {
            public $logs = [];
            public function log($level, $message, array $context = []): void
            {
                $this->logs[] = ['level' => $level, 'message' => $message, 'context' => $context];
            }
        };

        $body = 'not valid json';
        $signature = hash_hmac('sha256', $body, 'thisisasecret');
        $headers = ['X-Pusher-Key' => $this->auth_key, 'X-Pusher-Signature' => $signature];

        $this->pusher->setLogger($logger);

        try {
            $this->pusher->webhook($headers, $body);
        } catch (PusherException $e) {
        }

        self::assertCount(1, $logger->logs);
        self::assertIsArray($logger->logs[0]['context']);
    }

    public function testEncryptedEventWithNoMasterKeyLogsWithArrayContext(): void
    {
        $logger = new class extends AbstractLogger {
            public $logs = [];
            public function log($level, $message, array $context = []): void
            {
                $this->logs[] = ['level' => $level, 'message' => $message, 'context' => $context];
            }
        };

        $body = '{"time_ms":1530710011901,"events":[{"name":"client_event","channel":"private-encrypted-my-channel","event":"client-event","data":"anything"}]}';
        $signature = hash_hmac('sha256', $body, 'thisisasecret');
        $headers = ['X-Pusher-Key' => $this->auth_key, 'X-Pusher-Signature' => $signature];

        $this->pusher->setLogger($logger);
        $this->pusher->webhook($headers, $body);

        self::assertCount(1, $logger->logs);
        self::assertIsArray($logger->logs[0]['context']);
    }

    public function testEncryptedEventWithWrongKeyThrowsException(): void
    {
        // Note: decrypt_event() throws PusherException on wrong key rather than returning false,
        // so the $decryptedEvent === false branch in webhook() is dead code and the associated
        // log call cannot be reached via this path.
        $this->expectException(PusherException::class);

        $pusher = new Pusher(
            $this->auth_key,
            'thisisasecret',
            1,
            ['encryption_master_key_base64' => base64_encode(str_repeat('x', 32))]
        );

        $nonce = str_repeat("\0", SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $wrongKey = str_repeat('y', 32);
        $ciphertext = sodium_crypto_secretbox('{"test":"data"}', $nonce, $wrongKey);
        $encryptedData = json_encode([
            'nonce'      => base64_encode($nonce),
            'ciphertext' => base64_encode($ciphertext),
        ]);

        $body = json_encode([
            'time_ms' => 1530710011901,
            'events'  => [[
                'name'    => 'client_event',
                'channel' => 'private-encrypted-my-channel',
                'event'   => 'client-event',
                'data'    => $encryptedData,
            ]],
        ]);

        $signature = hash_hmac('sha256', $body, 'thisisasecret');
        $headers = ['X-Pusher-Key' => $this->auth_key, 'X-Pusher-Signature' => $signature];

        $pusher->webhook($headers, $body);
    }
}
