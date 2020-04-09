<?php

class PusherCryptoTest extends PHPUnit\Framework\TestCase
{
    protected function setUp()
    {
        if (function_exists('sodium_crypto_secretbox')) {
            $this->crypto = new Pusher\PusherCrypto('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa');
        } else {
            $this->markTestSkipped('libSodium is not available, so end to end encryption is not available.');
        }
    }

    public function testObjectConstruct()
    {
        $this->assertNotNull($this->crypto, 'Created new Pusher\PusherCrypto object');
    }

    public function testValidMasterEncryptionKeys()
    {
        $this->assertEquals(Pusher\PusherCrypto::parse_master_key('this is 32 bytes 123456789012345', ''), 'this is 32 bytes 123456789012345');
        $this->assertEquals(Pusher\PusherCrypto::parse_master_key("this key has nonprintable char \x00", ''), "this key has nonprintable char \x00");
        $this->assertEquals(Pusher\PusherCrypto::parse_master_key('', 'dGhpcyBpcyAzMiBieXRlcyAxMjM0NTY3ODkwMTIzNDU='), 'this is 32 bytes 123456789012345');
        $this->assertEquals(Pusher\PusherCrypto::parse_master_key('', 'dGhpcyBrZXkgaGFzIG5vbnByaW50YWJsZSBjaGFyIAA='), "this key has nonprintable char \x00");
    }

    /**
     * @expectedException \Pusher\PusherException
     * @expectedExceptionMessage both
     */
    public function testInvalidMasterEncryptionKeyTwoProvided()
    {
        Pusher\PusherCrypto::parse_master_key('this is 32 bytes 123456789012345', 'dGhpcyBpcyAzMiBieXRlcyAxMjM0NTY3ODkwMTIzNDU=');
    }

    /**
     * @expectedException \Pusher\PusherException
     * @expectedExceptionMessage 32 bytes
     */
    public function testInvalidMasterEncryptionKeyTooShort()
    {
        Pusher\PusherCrypto::parse_master_key('this is 31 bytes 12345678901234', '');
    }

    /**
     * @expectedException \Pusher\PusherException
     * @expectedExceptionMessage 32 bytes
     */
    public function testInvalidMasterEncryptionKeyTooLong()
    {
        Pusher\PusherCrypto::parse_master_key('this is 33 bytes 1234567890123456', '');
    }

    /**
     * @expectedException \Pusher\PusherException
     * @expectedExceptionMessage 32 bytes
     */
    public function testInvalidMasterEncryptionKeyBase64TooShort()
    {
        Pusher\PusherCrypto::parse_master_key('', 'dGhpcyBpcyAzMSBieXRlcyAxMjM0NTY3ODkwMTIzNA==');
    }

    /**
     * @expectedException \Pusher\PusherException
     * @expectedExceptionMessage 32 bytes
     */
    public function testInvalidMasterEncryptionKeyBase64TooLong()
    {
        Pusher\PusherCrypto::parse_master_key('', 'dGhpcyBpcyAzMyBieXRlcyAxMjM0NTY3ODkwMTIzNDU2');
    }

    /**
     * @expectedException \Pusher\PusherException
     * @expectedExceptionMessage valid base64
     */
    public function testInvalidMasterEncryptionKeyBase64InvalidBase64()
    {
        Pusher\PusherCrypto::parse_master_key('', 'dGhpcyBpcyAzMyBi!XRlcyAxMjM0NTY3ODkw#TIzNDU2');
    }

    public function testGenerateSharedSecret()
    {
        $expected = 'Rp+wpkNpL89qhqco1JkIG31AVXyU8PUVJBr1B2MvdoA=';
        // Check that the secret generation is generating consistent secrets
        $this->assertEquals(base64_encode($this->crypto->generate_shared_secret('private-encrypted-channel-a')), $expected);

        // Check that the secret generation is using the channel as a part of the generation
        $this->assertNotEquals(base64_encode($this->crypto->generate_shared_secret('private-encrypted-channel-b')), $expected);

        // Check that specifying a different key results in a different result
        $crypto2 = new Pusher\PusherCrypto('bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb');
        $this->assertNotEquals(base64_encode($crypto2->generate_shared_secret('private-encrypted-channel-a')), $expected);
    }

    /**
     * @expectedException \Pusher\PusherException
     */
    public function testGenerateSharedSecretNoChannel()
    {
        $this->crypto->generate_shared_secret('');
    }

    public function testIsEncryptedChannel()
    {
        $this->assertEquals(Pusher\PusherCrypto::is_encrypted_channel('private-encrypted-test'), true);
        $this->assertEquals(Pusher\PusherCrypto::is_encrypted_channel('private-encrypted'), false);
        $this->assertEquals(Pusher\PusherCrypto::is_encrypted_channel('test-private-encrypted'), false);
    }

    public function testEncryptDecryptEventValid()
    {
        $channel = 'private-encrypted-bla';
        $payload = "now that's what I call a payload!";
        $encrypted_payload = $this->crypto->encrypt_payload($channel, $payload);
        $this->assertNotNull($encrypted_payload);

        // Create a mock Event object
        $event = new stdClass();
        $event->data = $encrypted_payload;
        $event->channel = $channel;
        $decrypted_event = $this->crypto->decrypt_event($event);
        $decrypted_payload = $decrypted_event->data;
        $this->assertEquals($decrypted_payload, $payload);
    }

    /**
     * @expectedException \Pusher\PusherException
     */
    public function testEncryptPayloadNoChannel()
    {
        $channel = '';
        $payload = "now that's what I call a payload!";
        $encrypted_payload = $this->crypto->encrypt_payload($channel, $payload);
        $this->assertEquals($encrypted_payload, false);
    }

    /**
     * @expectedException \Pusher\PusherException
     */
    public function testEncryptPayloadPublicChannel()
    {
        $channel = 'public-static-void-main';
        $payload = "now that's what I call a payload!";
        $encrypted_payload = $this->crypto->encrypt_payload($channel, $payload);
        $this->assertEquals($encrypted_payload, false);
    }

    /**
     * @expectedException \Pusher\PusherException
     */
    public function testDecryptPayloadWrongKey()
    {
        $channel = 'private-encrypted-bla';
        $payload = "now that's what I call a payload!";
        $encrypted_payload = $this->crypto->encrypt_payload($channel, $payload);
        $this->assertNotNull($encrypted_payload);
        // create empty object with no properties
        $event = new stdClass();
        $event->data = $encrypted_payload;
        $event->channel = $channel;

        $crypto2 = new Pusher\PusherCrypto('bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb');
        $decrypted_event = $crypto2->decrypt_event($event);
        $this->assertEquals($decrypted_event, false);
    }
}
