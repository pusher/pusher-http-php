<?php

namespace unit;

use PHPUnit\Framework\TestCase;
use Pusher\PusherCrypto;
use stdClass;

class CryptoTest extends TestCase
{
    /**
     * @var PusherCrypto
     */
    private $crypto;

    protected function setUp(): void
    {
        if (function_exists('sodium_crypto_secretbox')) {
            $this->crypto = new PusherCrypto('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa');
        } else {
            self::markTestSkipped('libSodium is not available, so end to end encryption is not available.');
        }
    }

    public function testObjectConstruct(): void
    {
        self::assertNotNull($this->crypto, 'Created new \Pusher\PusherCrypto object');
    }

    public function testValidMasterEncryptionKeys(): void
    {
        self::assertEquals('this is 32 bytes 123456789012345', PusherCrypto::parse_master_key('dGhpcyBpcyAzMiBieXRlcyAxMjM0NTY3ODkwMTIzNDU='));
        self::assertEquals("this key has nonprintable char \x00", PusherCrypto::parse_master_key('dGhpcyBrZXkgaGFzIG5vbnByaW50YWJsZSBjaGFyIAA='));
    }

    public function testInvalidMasterEncryptionKeyTooShort(): void
    {
        $this->expectException(\Pusher\PusherException::class);
        $this->expectExceptionMessage('32 bytes');

        PusherCrypto::parse_master_key('dGhpcyBpcyAzMSBieXRlcyAxMjM0NTY3ODkwMTIzNA==');
    }

    public function testInvalidMasterEncryptionKeyTooLong(): void
    {
        $this->expectException(\Pusher\PusherException::class);
        $this->expectExceptionMessage('32 bytes');

        PusherCrypto::parse_master_key('dGhpcyBpcyAzMSBieXRlcyAxMjM0NTY3ODkwMTIzNDU2');
    }

    public function testInvalidMasterEncryptionKeyBase64TooShort(): void
    {
        $this->expectException(\Pusher\PusherException::class);
        $this->expectExceptionMessage('32 bytes');

        PusherCrypto::parse_master_key('dGhpcyBpcyAzMSBieXRlcyAxMjM0NTY3ODkwMTIzNA==');
    }

    public function testInvalidMasterEncryptionKeyBase64TooLong(): void
    {
        $this->expectException(\Pusher\PusherException::class);
        $this->expectExceptionMessage('32 bytes');

        PusherCrypto::parse_master_key('dGhpcyBpcyAzMyBieXRlcyAxMjM0NTY3ODkwMTIzNDU2');
    }

    public function testInvalidMasterEncryptionKeyBase64InvalidBase64(): void
    {
        $this->expectException(\Pusher\PusherException::class);
        $this->expectExceptionMessage('valid base64');

        PusherCrypto::parse_master_key('dGhpcyBpcyAzMyBi!XRlcyAxMjM0NTY3ODkw#TIzNDU2');
    }

    public function testGenerateSharedSecret(): void
    {
        $expected = 'Rp+wpkNpL89qhqco1JkIG31AVXyU8PUVJBr1B2MvdoA=';
        // Check that the secret generation is generating consistent secrets
        self::assertEquals($expected, base64_encode($this->crypto->generate_shared_secret('private-encrypted-channel-a')));

        // Check that the secret generation is using the channel as a part of the generation
        self::assertNotEquals($expected, base64_encode($this->crypto->generate_shared_secret('private-encrypted-channel-b')));

        // Check that specifying a different key results in a different result
        $crypto2 = new PusherCrypto('bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb');
        self::assertNotEquals($expected, base64_encode($crypto2->generate_shared_secret('private-encrypted-channel-a')));
    }

    public function testGenerateSharedSecretNoChannel(): void
    {
        $this->expectException(\Pusher\PusherException::class);

        $this->crypto->generate_shared_secret('');
    }

    public function testIsEncryptedChannel(): void
    {
        self::assertEquals(true, PusherCrypto::is_encrypted_channel('private-encrypted-test'));
        self::assertEquals(false, PusherCrypto::is_encrypted_channel('private-encrypted'));
        self::assertEquals(false, PusherCrypto::is_encrypted_channel('test-private-encrypted'));
    }

    public function testHasMixedChannels(): void
    {
        self::assertEquals(false, PusherCrypto::has_mixed_channels(['private-encrypted-test']));
        self::assertEquals(false, PusherCrypto::has_mixed_channels(['another']));
        self::assertEquals(true, PusherCrypto::has_mixed_channels(['private-encrypted-test', 'another']));
        self::assertEquals(false, PusherCrypto::has_mixed_channels(['private-encrypted-test', 'private-encrypted-another']));
        self::assertEquals(false, PusherCrypto::has_mixed_channels(['test', 'another']));
    }

    public function testEncryptDecryptEventValid(): void
    {
        $channel = 'private-encrypted-bla';
        $payload = "now that's what I call a payload!";
        $encrypted_payload = $this->crypto->encrypt_payload($channel, $payload);
        self::assertNotNull($encrypted_payload);

        // Create a mock Event object
        $event = new stdClass();
        $event->data = $encrypted_payload;
        $event->channel = $channel;
        $decrypted_event = $this->crypto->decrypt_event($event);
        $decrypted_payload = $decrypted_event->data;
        self::assertEquals($payload, $decrypted_payload);
    }

    public function testEncryptPayloadNoChannel(): void
    {
        $this->expectException(\Pusher\PusherException::class);

        $channel = '';
        $payload = "now that's what I call a payload!";
        $encrypted_payload = $this->crypto->encrypt_payload($channel, $payload);
        self::assertEquals(false, $encrypted_payload);
    }

    public function testEncryptPayloadPublicChannel(): void
    {
        $this->expectException(\Pusher\PusherException::class);

        $channel = 'public-static-void-main';
        $payload = "now that's what I call a payload!";
        $encrypted_payload = $this->crypto->encrypt_payload($channel, $payload);
        self::assertEquals(false, $encrypted_payload);
    }

    public function testDecryptPayloadWrongKey(): void
    {
        $this->expectException(\Pusher\PusherException::class);

        $channel = 'private-encrypted-bla';
        $payload = "now that's what I call a payload!";
        $encrypted_payload = $this->crypto->encrypt_payload($channel, $payload);
        self::assertNotNull($encrypted_payload);
        // create empty object with no properties
        $event = new stdClass();
        $event->data = $encrypted_payload;
        $event->channel = $channel;

        $crypto2 = new PusherCrypto('bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb');
        $decrypted_event = $crypto2->decrypt_event($event);
        self::assertEquals(false, $decrypted_event);
    }
}
