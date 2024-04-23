<?php

namespace Pusher;

class PusherCrypto
{
    private $encryption_master_key;

    // The prefix any e2e channel must have
    public const ENCRYPTED_PREFIX = 'private-encrypted-';

    public const MULTI_PREFIX = 'private-encrypted-multi-';

    /**
     * Checks if a given channel is an encrypted channel.
     *
     * @param string $channel the name of the channel
     *
     * @return bool true if channel is an encrypted channel
     */
    public static function is_encrypted_channel(string $channel): bool
    {
        return strpos($channel, self::ENCRYPTED_PREFIX) === 0;
    }

    /**
     * Checks if a given channel is an encrypted channel.
     *
     * @param string $channel the name of the channel
     *
     * @return bool true if channel is an encrypted channel
     */
    public static function is_multi_encrypted_channel(string $channel): bool
    {
        return strpos($channel, self::MULTI_PREFIX) === 0;
    }

    /**
     * Checks if channels are a mix of encrypted and non-encrypted types.
     *
     * @param  array  $channels
     * @return bool true when mixed channel types are discovered
     */
    public static function has_mixed_channels(array $channels): bool
    {
        $unencrypted_seen = false;
        $encrypted_seen = false;

        foreach ($channels as $channel) {
            if(self::is_encrypted_channel($channel)) {
                if ($unencrypted_seen) {
                    return true;
                } else {
                    $encrypted_seen = true;
                }
            } else {
                if ($encrypted_seen) {
                    return true;
                } else {
                    $unencrypted_seen = true;
                }
            }
        }

        return false;
    }

    /**
     * @param $encryption_master_key_base64
     * @return string
     * @throws PusherException
     */
    public static function parse_master_key($encryption_master_key_base64): string
    {
        if (!function_exists('sodium_crypto_secretbox')) {
            throw new PusherException('To use end to end encryption, you must either be using PHP 7.2 or greater or have installed the libsodium-php extension for php < 7.2.');
        }

        if ($encryption_master_key_base64 !== '') {
            $decoded_key = base64_decode($encryption_master_key_base64, true);
            if ($decoded_key === false) {
                throw new PusherException('encryption_master_key_base64 must be a valid base64 string');
            }

            if (strlen($decoded_key) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
                throw new PusherException('encryption_master_key_base64 must encode a key which is 32 bytes long');
            }

            return $decoded_key;
        }

        return '';
    }

    /**
     * Initialises a PusherCrypto instance.
     *
     * @param string $encryption_master_key the SECRET_KEY_LENGTH key that will be used for key derivation.
     */
    public function __construct(string $encryption_master_key)
    {
        $this->encryption_master_key = $encryption_master_key;
    }

    /**
     * Decrypts a given event.
     *
     * @param object $event an object that has an encrypted data property and a channel property.
     *
     * @return object the event with a decrypted payload, or false if decryption was unsuccessful.
     * @throws PusherException
     */
    public function decrypt_event(object $event): object
    {
        if (self::is_multi_encrypted_channel($event->data)) {
            $parsed_payload = $this->parse_multi_encrypted_message($event->data);
            $shared_secret = $this->multi_channel_secret($parsed_payload->channels, $parsed_payload->random);
        } else {
            $parsed_payload = $this->parse_encrypted_message($event->data);
            $shared_secret = $this->generate_shared_secret($event->channel);
        }
        $decrypted_payload = $this->decrypt_payload($parsed_payload->ciphertext, $parsed_payload->nonce, $shared_secret);
        if (!$decrypted_payload) {
            throw new PusherException('Decryption of the payload failed. Wrong key?');
        }
        $event->data = $decrypted_payload;

        return $event;
    }

    /**
     * Encode multiple channel names into a parseable header
     *
     * @param array $channels
     * @param string $random
     * @return string
     *
     * @throws \SodiumException
     */
    public function multi_channel_encode(array $channels, string $random = ''): string
    {
        // Determine a stable order of channel names:
        $sorted = array_values($channels);
        sort($sorted);

        $list = [];
        $pos = strlen(self::ENCRYPTED_PREFIX);
        foreach ($sorted as $ch) {
            if (!self::is_encrypted_channel($ch)) {
                continue;
            }
            // Strip off encrypted prefix:
            $list []= substr($ch, $pos);
        }
        $flat = json_encode(['c' => $list, 'r' => base64_decode($random)]);
        return self::MULTI_PREFIX . sodium_bin2hex(pack('J', strlen($flat)) . $flat);
    }

    /**
     * Decode the header into a list of encrypted channel names
     *
     * @param string $header
     * @return array
     * @throws PusherException
     * @throws \SodiumException
     */
    public function multi_channel_decode(string $header): array
    {
        // multi-{hex}, validate "multi-"
        $len = strlen(self::MULTI_PREFIX);
        $multi = substr($header, 0, $len);
        if (!hash_equals($multi, self::MULTI_PREFIX)) {
            throw new PusherException('Not a multi-channel');
        }
        // decode {hex}
        $hex_decoded = sodium_hex2bin(substr($header, $len));
        if (strlen($hex_decoded) < 8) {
            throw new PusherException('Multi-channel name must be at least 8 characters');
        }

        // |json|, json
        $json_len = unpack('J', substr($hex_decoded, 0, 8))[1];
        $json = substr($hex_decoded,  8);
        if (strlen($json) !== $json_len) {
            throw new PusherException('Invalid channel length');
        }
        // JSON-decode
        $decoded = json_decode($json, JSON_THROW_ON_ERROR);
        $random = base64_decode($decoded['r'] ?? '');
        // Re-assemble actual channel names:
        $channels = [];
        foreach ($decoded['c'] as $c) {
            $channels [] = self::ENCRYPTED_PREFIX . $c;
        }
        return [$channels, $random];
    }

    /**
     * Derive a secret for broadcasting to multiple channels
     *
     * Algorithm: HMAC-SHA256
     *
     * @param string[] $channels
     * @param string|null $random
     * @return string
     * @throws PusherException
     */
    public function multi_channel_secret(array $channels, ?string $random = ''): string
    {
        // Determine a stable order of channel names:
        $sorted = array_values($channels);
        sort($sorted);
        $secrets = [];

        // Get the secret for each channel:
        foreach ($sorted as $channel) {
            $secrets []= self::generate_shared_secret($channel);
        }
        $count = count($sorted);
        $sha = hash_init('sha256', HASH_HMAC, $this->encryption_master_key);
        // Begin with randomness:
        hash_update($sha, pack('J', strlen($random)));
        hash_update($sha, $random);
        // Prepend the hash of the number of elements:
        hash_update($sha, pack('J', $count));
        for ($i = 0; $i < $count; ++$i) {
            // update hash with ... |channelname|, channelname, |secret|, secret
            hash_update($sha, pack('J', strlen($sorted[$i])));
            hash_update($sha, $sorted[$i]);
            hash_update($sha, pack('J', strlen($secrets[$i])));
            hash_update($sha, $secrets[$i]);
        }
        return hash_final($sha, true);
    }

    /**
     * Derives a shared secret from the secret key and the channel to broadcast to.
     *
     * @param string $channel the name of the channel
     *
     * @return string a SHA256 hash (encoded as base64) of the channel name appended to the encryption key
     * @throws PusherException
     */
    public function generate_shared_secret(string $channel): string
    {
        if (!self::is_encrypted_channel($channel)) {
            throw new PusherException('You must specify a channel of the form private-encrypted-* for E2E encryption. Got ' . $channel);
        }

        return hash('sha256', $channel . $this->encryption_master_key, true);
    }

    /**
     * Encrypts a given plaintext for broadcast on a particular channel.
     *
     * @param string[] $channels the names of the channel the payloads event will be broadcast on
     * @param string $plaintext the data to encrypt
     *
     * @return string a string ready to be sent as the data of an event.
     * @throws PusherException
     * @throws \SodiumException
     * @throws \JsonException
     */
    public function encrypt_payload_multi(array $channels, string $plaintext): string
    {
        $secret = $this->multi_channel_secret($channels);
        $header = $this->multi_channel_encode($channels);
        $nonce = $this->generate_nonce();
        $cipher_text = sodium_crypto_secretbox($plaintext, $nonce, $secret);

        return $header . ':' . $this->format_encrypted_message($nonce, $cipher_text);
    }

    /**
     * Encrypts a given plaintext for broadcast on a particular channel.
     *
     * @param string $channel the name of the channel the payloads event will be broadcast on
     * @param string $plaintext the data to encrypt
     *
     * @return string a string ready to be sent as the data of an event.
     * @throws PusherException
     * @throws \SodiumException
     */
    public function encrypt_payload(string $channel, string $plaintext): string
    {
        if (!self::is_encrypted_channel($channel)) {
            throw new PusherException('Cannot encrypt plaintext for a channel that is not of the form private-encrypted-*. Got ' . $channel);
        }
        $nonce = $this->generate_nonce();
        $shared_secret = $this->generate_shared_secret($channel);
        $cipher_text = sodium_crypto_secretbox($plaintext, $nonce, $shared_secret);

        try {
            return $this->format_encrypted_message($nonce, $cipher_text);
        } catch (\JsonException $e) {
            throw new PusherException('Data encoding error.');
        }
    }

    /**
     * Decrypts a given payload using the nonce and shared secret.
     *
     * @param string $payload the ciphertext
     * @param string $nonce the nonce used in the encryption
     * @param string $shared_secret the shared_secret used in the encryption
     *
     * @return string plaintext
     * @throws \SodiumException
     */
    public function decrypt_payload(string $payload, string $nonce, string $shared_secret)
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
     * @param string $nonce the nonce used in the encryption process (bytes)
     * @param string $ciphertext the ciphertext (bytes)
     *
     * @return string JSON with base64 encoded nonce and ciphertext`
     * @throws \JsonException
     */
    private function format_encrypted_message(string $nonce, string $ciphertext): string
    {
        $encrypted_message = new \stdClass();
        $encrypted_message->nonce = base64_encode($nonce);
        $encrypted_message->ciphertext = base64_encode($ciphertext);

        return json_encode($encrypted_message, JSON_THROW_ON_ERROR);
    }

    /**
     * @param string $payload
     * @return object
     * @throws PusherException
     * @throws \SodiumException
     */
    private function parse_multi_encrypted_message(string $payload): object
    {
        $split = strpos($payload, ':');
        $header = substr($payload, 0, $split);
        $payload = substr($payload,  $split + 1);
        $decoded = $this->parse_encrypted_message($payload);
        if (!is_object($decoded)) {
            throw new PusherException(
                'Invalid encrypted message: expected an object, got ' . gettype($decoded)
            );
        }
        [$channels, $random] = $this->multi_channel_decode($header);
        $decoded->channels = $channels;
        $decoded->random = $random;
        return $decoded;
    }

    /**
     * Parses an encrypted message into its nonce and ciphertext components.
     *
     *
     * @param string $payload the encrypted message payload
     *
     * @return object php object with decoded nonce and ciphertext
     * @throws PusherException
     */
    private function parse_encrypted_message(string $payload): object
    {
        try {
            $decoded_payload = json_decode($payload, false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new PusherException('Data decoding error.');
        }

        $decoded_payload->nonce = base64_decode($decoded_payload->nonce);
        $decoded_payload->ciphertext = base64_decode($decoded_payload->ciphertext);
        if ($decoded_payload->ciphertext === '' || strlen($decoded_payload->nonce) !== SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) {
            throw new PusherException('Received a payload that cannot be parsed.');
        }

        return $decoded_payload;
    }

    /**
     * Generates a nonce that is SODIUM_CRYPTO_SECRETBOX_NONCEBYTES long.
     * @return string
     * @throws \Exception
     */
    private function generate_nonce(): string
    {
        return random_bytes(
            SODIUM_CRYPTO_SECRETBOX_NONCEBYTES
        );
    }
}
