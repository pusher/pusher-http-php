<?php

namespace Pusher;

class PusherCrypto
{
    private $encryption_key = '';

    // The prefix any e2e channel must have
    const ENCRYPTED_PREFIX = 'private-encrypted-';

    // The exact length the user specified key must be
    const SECRET_KEY_LENGTH = 32;

    /**
     * Checks if a given channel is an encrypted channel.
     *
     * @param string $channel the name of the channel
     *
     * @return bool true if channel is an encrypted channel
     */
    public static function is_encrypted_channel($channel)
    {
        return substr($channel, 0, strlen(self::ENCRYPTED_PREFIX)) === self::ENCRYPTED_PREFIX;
    }

    /**
     * Initialises a PusherCrypto instance.
     *
     * @param string $encryption_key the SECRET_KEY_LENGTH key that will be used for key derivation.
     */
    public function __construct($encryption_key)
    {
        if (function_exists('sodium_crypto_secretbox')) {
            if (strlen($encryption_key) === SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
                $this->encryption_key = $encryption_key;

                return;
            } else {
                throw new PusherException('Your end to end encryption key must be 32 chars long');
            }
        }

        throw new PusherException('To use end to end encryption, you must either be using PHP 7.2 or greater or have installed the libsodium-php extension for php < 7.2.');
    }

    /**
     * Decrypts a given event.
     *
     * @param object $event an object that has an encrypted data property and a channel property.
     *
     * @return object the event with a decrypted payload, or false if decryption was unsuccessful.
     */
    public function decrypt_event($event)
    {
        $encrypted_payload = explode(':', $event->data);
        $nonce = base64_decode($encrypted_payload[1]);
        $payload = base64_decode($encrypted_payload[2]);
        $shared_secret = $this->generate_shared_secret($event->channel, $this->encryption_key);
        $decrypted_payload = $this->decrypt_payload($payload, $nonce, $shared_secret);
        if (!$decrypted_payload) {
            return false;
        }
        $event->data = $decrypted_payload;

        return $event;
    }

    /**
     * Derives a shared secret from the secret key and the channel to broadcast to.
     *
     * @param string $channel the name of the channel
     *
     * @return string a SHA256 hash (encoded as base64) of the channel name appended to the encryption key
     */
    public function generate_shared_secret($channel)
    {
        if ($channel == '') {
            return false;
        }

        return hash('sha256', $channel.$this->encryption_key, true);
    }

    /**
     * Encrypts a given plaintext for broadcast on a particular channel.
     *
     * @param string $channel   the name of the channel the payloads event will be broadcast on
     * @param string $plaintext the data to encrypt
     *
     * @return string a string ready to be sent as the data of an event.
     */
    public function encrypt_payload($channel, $plaintext)
    {
        if (!self::is_encrypted_channel($channel)) {
            return false;
        }
        $nonce = $this->generate_nonce();
        $nonce_b64 = base64_encode($nonce);
        $shared_secret = $this->generate_shared_secret($channel);
        $cipher_text_b64 = base64_encode(sodium_crypto_secretbox($plaintext, $nonce, $shared_secret));

        return $this->format_encrypted_message($nonce_b64, $cipher_text_b64);
    }

    /**
     * Decrypts a given payload using the nonce and shared secret.
     *
     * @param string $payload       the ciphertext
     * @param string $nonce         the nonce used in the encryption
     * @param string $shared_secret the shared_secret used in the encryption
     *
     * @return string plaintext
     */
    public function decrypt_payload($payload, $nonce, $shared_secret)
    {
        $plaintext = sodium_crypto_secretbox_open($payload, $nonce, $shared_secret);
        if (empty($plaintext)) {
            return false;
        }

        return $plaintext;
    }

    /**
     * Formats an encrypted message ready for broadcast.
     *
     * @param string $nonce the nonce in the encryption
     * @param string $data  the ciphertext
     */
    private function format_encrypted_message($nonce, $payload)
    {
        return sprintf('encrypted_data:%s:%s', $nonce, $payload);
    }

    /**
     * Generates a nonce that is SODIUM_CRYPTO_SECRETBOX_NONCEBYTES long.
     */
    private function generate_nonce()
    {
        return random_bytes(
            SODIUM_CRYPTO_SECRETBOX_NONCEBYTES
        );
    }
}
