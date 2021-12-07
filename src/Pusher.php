<?php

namespace Pusher;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Promise\PromiseInterface;

class Pusher implements LoggerAwareInterface, PusherInterface
{
    use LoggerAwareTrait;

    /**
     * @var string Version
     */
    public static $VERSION = '7.0.2';

    /**
     * @var null|PusherCrypto
     */
    private $crypto;

    /**
     * @var array Settings
     */
    private $settings = [
        'scheme'                => 'http',
        'port'                  => 80,
        'path'                  => '',
        'timeout'               => 30,
    ];

    /**
     * @var null|resource
     */
    private $client = null; // Guzzle client

    /**
     * Initializes a new Pusher instance with key, secret, app ID and channel.
     *
     * @param string $auth_key
     * @param string $secret
     * @param string $app_id
     * @param array $options  [optional]
     *                         Options to configure the Pusher instance.
     *                         scheme - e.g. http or https
     *                         host - the host e.g. api-mt1.pusher.com. No trailing forward slash.
     *                         port - the http port
     *                         timeout - the http timeout
     *                         useTLS - quick option to use scheme of https and port 443 (default is true).
     *                         cluster - cluster name to connect to.
     *                         encryption_master_key_base64 - a 32 byte key, encoded as base64. This key, along with the channel name, are used to derive per-channel encryption keys. Per-channel keys are used to encrypt event data on encrypted channels.
     * @param ClientInterface|null $client [optional] - a Guzzle client to use for all HTTP requests
     *
     * @throws PusherException Throws exception if any required dependencies are missing
     */
    public function __construct(string $auth_key, string $secret, string $app_id, array $options = [], ClientInterface $client = null)
    {
        $this->check_compatibility();

        if (!is_null($client)) {
            $this->client = $client;
        } else {
            $this->client = new \GuzzleHttp\Client();
        }

        $useTLS = true;
        if (isset($options['useTLS'])) {
            $useTLS = $options['useTLS'] === true;
        }
        if (
            $useTLS &&
            !isset($options['scheme']) &&
            !isset($options['port'])
        ) {
            $options['scheme'] = 'https';
            $options['port'] = 443;
        }

        $this->settings['auth_key'] = $auth_key;
        $this->settings['secret'] = $secret;
        $this->settings['app_id'] = $app_id;
        $this->settings['base_path'] = '/apps/' . $this->settings['app_id'];

        foreach ($options as $key => $value) {
            // only set if valid setting/option
            if (isset($this->settings[$key])) {
                $this->settings[$key] = $value;
            }
        }

        // handle the case when 'host' and 'cluster' are specified in the options.
        if (!array_key_exists('host', $this->settings)) {
            if (array_key_exists('host', $options)) {
                $this->settings['host'] = $options['host'];
            } elseif (array_key_exists('cluster', $options)) {
                $this->settings['host'] = 'api-' . $options['cluster'] . '.pusher.com';
            } else {
                $this->settings['host'] = 'api-mt1.pusher.com';
            }
        }

        // ensure host doesn't have a scheme prefix
        $this->settings['host'] = preg_replace('/http[s]?\:\/\//', '', $this->settings['host'], 1);

        if (!array_key_exists('encryption_master_key_base64', $options)) {
            $options['encryption_master_key_base64'] = '';
        }

        if ($options['encryption_master_key_base64'] !== '') {
            $parsedKey = PusherCrypto::parse_master_key(
                $options['encryption_master_key_base64']
            );
            $this->crypto = new PusherCrypto($parsedKey);
        }
    }

    /**
     * Fetch the settings.
     *
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * Log a string.
     *
     * @param string           $msg     The message to log
     * @param array|\Exception $context [optional] Any extraneous information that does not fit well in a string.
     * @param string           $level   [optional] Importance of log message, highly recommended to use Psr\Log\LogLevel::{level}
     */
    private function log(string $msg, array $context = [], string $level = LogLevel::DEBUG): void
    {
        if (is_null($this->logger)) {
            return;
        }

        if ($this->logger instanceof LoggerInterface) {
            $this->logger->log($level, $msg, $context);

            return;
        }

        // Support old style logger (deprecated)
        $msg = sprintf('Pusher: %s: %s', strtoupper($level), $msg);
        $replacement = [];

        foreach ($context as $k => $v) {
            $replacement['{' . $k . '}'] = $v;
        }

        $this->logger->log($level, strtr($msg, $replacement));
    }

    /**
     * Check if the current PHP setup is sufficient to run this class.
     *
     * @throws PusherException If any required dependencies are missing
     */
    private function check_compatibility(): void
    {
        if (!extension_loaded('json')) {
            throw new PusherException('The Pusher library requires the PHP JSON module. Please ensure it is installed');
        }

        if (!in_array('sha256', hash_algos(), true)) {
            throw new PusherException('SHA256 appears to be unsupported - make sure you have support for it, or upgrade your version of PHP.');
        }
    }

    /**
     * Validate number of channels and channel name format.
     *
     * @param string[] $channels An array of channel names to validate
     *
     * @throws PusherException If $channels is too big or any channel is invalid
     */
    private function validate_channels(array $channels): void
    {
        if (count($channels) > 100) {
            throw new PusherException('An event can be triggered on a maximum of 100 channels in a single call.');
        }

        foreach ($channels as $channel) {
            $this->validate_channel($channel);
        }
    }

    /**
     * Ensure a channel name is valid based on our spec.
     *
     * @param string $channel The channel name to validate
     *
     * @throws PusherException If $channel is invalid
     */
    private function validate_channel(string $channel): void
    {
        if (!preg_match('/\A[-a-zA-Z0-9_=@,.;]+\z/', $channel)) {
            throw new PusherException('Invalid channel name ' . $channel);
        }
    }

    /**
     * Ensure a socket_id is valid based on our spec.
     *
     * @param string $socket_id The socket ID to validate
     *
     * @throws PusherException If $socket_id is invalid
     */
    private function validate_socket_id(string $socket_id): void
    {
        if ($socket_id !== null && !preg_match('/\A\d+\.\d+\z/', $socket_id)) {
            throw new PusherException('Invalid socket ID ' . $socket_id);
        }
    }

    /**
     * Utility function used to generate signing headers
     *
     * @param string $path
     * @param string $request_method
     * @param array $query_params [optional]
     *
     * @return array
     */
    private function sign(string $path, string $request_method = 'GET', array $query_params = []): array
    {
        return self::build_auth_query_params(
            $this->settings['auth_key'],
            $this->settings['secret'],
            $request_method,
            $path,
            $query_params
        );
    }

    /**
     * Build the Channels url prefix.
     *
     * @return string
     */
    private function channels_url_prefix(): string
    {
        return $this->settings['scheme'] . '://' . $this->settings['host'] . ':' . $this->settings['port'] . $this->settings['path'];
    }

    /**
     * Build the required HMAC'd auth string.
     *
     * @param string $auth_key
     * @param string $auth_secret
     * @param string $request_method
     * @param string $request_path
     * @param array $query_params [optional]
     * @param string $auth_version [optional]
     * @param string|null $auth_timestamp [optional]
     * @return array
     */
    public static function build_auth_query_params(
        string $auth_key,
        string $auth_secret,
        string $request_method,
        string $request_path,
        array $query_params = [],
        string $auth_version = '1.0',
        string $auth_timestamp = null
    ): array {
        $params = [];
        $params['auth_key'] = $auth_key;
        $params['auth_timestamp'] = (is_null($auth_timestamp) ? time() : $auth_timestamp);
        $params['auth_version'] = $auth_version;

        $params = array_merge($params, $query_params);
        ksort($params);

        $string_to_sign = "$request_method\n" . $request_path . "\n" . self::array_implode('=', '&', $params);

        $auth_signature = hash_hmac('sha256', $string_to_sign, $auth_secret, false);

        $params['auth_signature'] = $auth_signature;

        return $params;
    }

    /**
     * Implode an array with the key and value pair giving
     * a glue, a separator between pairs and the array
     * to implode.
     *
     * @param string       $glue      The glue between key and value
     * @param string       $separator Separator between pairs
     * @param array|string $array     The array to implode
     *
     * @return string The imploded array
     */
    public static function array_implode(string $glue, string $separator, $array): string
    {
        if (!is_array($array)) {
            return $array;
        }

        $string = [];
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $val = implode(',', $val);
            }
            $string[] = "{$key}{$glue}{$val}";
        }

        return implode($separator, $string);
    }

    /**
     * Helper function to prepare trigger request. Takes the same
     * parameters as the public trigger functions.
     *
     * @param array|string $channels A channel name or an array of channel names to publish the event on.
     * @param string $event
     * @param mixed $data Event data
     * @param array $params [optional]
     * @param bool $already_encoded [optional]
     *
     * @return Request
     * @throws PusherException Throws PusherException if $channels is an array of size 101 or above or $socket_id is invalid
     */
    public function make_request($channels, string $event, $data, array $params = [], bool $already_encoded = false): Request
    {
        if (is_string($channels) === true) {
            $channels = [$channels];
        }

        $this->validate_channels($channels);
        if (isset($params['socket_id'])) {
            $this->validate_socket_id($params['socket_id']);
        }

        $has_encrypted_channel = false;
        foreach ($channels as $chan) {
            if (PusherCrypto::is_encrypted_channel($chan)) {
                $has_encrypted_channel = true;
                break;
            }
        }

        if ($has_encrypted_channel) {
            if (count($channels) > 1) {
                // For rationale, see limitations of end-to-end encryption in the README
                throw new PusherException('You cannot trigger to multiple channels when using encrypted channels');
            } else {
                try {
                    $data_encoded = $this->crypto->encrypt_payload(
                        $channels[0],
                        $already_encoded ? $data : json_encode($data, JSON_THROW_ON_ERROR)
                    );
                } catch (\JsonException $e) {
                    throw new PusherException('Data encoding error.');
                }
            }
        } else {
            try {
                $data_encoded = $already_encoded ? $data : json_encode($data, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                throw new PusherException('Data encoding error.');
            }
        }

        $query_params = [];

        $path = $this->settings['base_path'] . '/events';

        // json_encode might return false on failure
        if (!$data_encoded) {
            $this->log('Failed to perform json_encode on the the provided data: {error}', [
                'error' => print_r($data, true),
            ], LogLevel::ERROR);
        }

        $post_params = [];
        $post_params['name'] = $event;
        $post_params['data'] = $data_encoded;
        $post_params['channels'] = array_values($channels);

        $all_params = array_merge($post_params, $params);

        try {
            $post_value = json_encode($all_params, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new PusherException('Data encoding error.');
        }

        $query_params['body_md5'] = md5($post_value);

        $signature = $this->sign($path, 'POST', $query_params);

        $this->log('trigger POST: {post_value}', compact('post_value'));

        $headers = [
            'Content-Type' => 'application/json',
            'X-Pusher-Library' => 'pusher-http-php ' . self::$VERSION
        ];

        $params = array_merge($signature, $query_params);
        $query_string = self::array_implode('=', '&', $params);
        $full_path = $path . "?" . $query_string;
        return new Request('POST', $full_path, $headers, $post_value);
    }

    /**
     * Trigger an event by providing event name and payload.
     * Optionally provide a socket ID to exclude a client (most likely the sender).
     *
     * @param array|string $channels A channel name or an array of channel names to publish the event on.
     * @param string $event
     * @param mixed $data Event data
     * @param array $params [optional]
     * @param bool $already_encoded [optional]
     *
     * @return object
     * @throws ApiErrorException Throws ApiErrorException if the Channels HTTP API responds with an error
     * @throws GuzzleException
     * @throws PusherException Throws PusherException if $channels is an array of size 101 or above or $socket_id is invalid
     */
    public function trigger($channels, string $event, $data, array $params = [], bool $already_encoded = false): object
    {
        $request = $this->make_request($channels, $event, $data, $params, $already_encoded);

        try {
            $response = $this->client->send($request, [
                'http_errors' => false,
                'base_uri' => $this->channels_url_prefix()
            ]);
        } catch (ConnectException $e) {
            throw new ApiErrorException($e->getMessage());
        }

        $status = $response->getStatusCode();

        if ($status !== 200) {
            $body = (string) $response->getBody();
            throw new ApiErrorException($body, $status);
        }

        try {
            $result = json_decode($response->getBody(), false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new PusherException('Data encoding error.');
        }

        if (property_exists($result, 'channels')) {
            $result->channels = get_object_vars($result->channels);
        }

        return $result;
    }

    /**
     * Asynchronously trigger an event by providing event name and payload.
     * Optionally provide a socket ID to exclude a client (most likely the sender).
     *
     * @param array|string $channels A channel name or an array of channel names to publish the event on.
     * @param string $event
     * @param mixed $data Event data
     * @param array $params [optional]
     * @param bool $already_encoded [optional]
     *
     * @return PromiseInterface
     * @throws PusherException
     */
    public function triggerAsync($channels, string $event, $data, array $params = [], bool $already_encoded = false): PromiseInterface
    {
        $request = $this->make_request($channels, $event, $data, $params, $already_encoded);

        $promise = $this->client->sendAsync($request, [
            'http_errors' => false,
            'base_uri' => $this->channels_url_prefix()
        ])->then(function ($response) {
            $status = $response->getStatusCode();

            if ($status !== 200) {
                $body = (string) $response->getBody();
                throw new ApiErrorException($body, $status);
            }

            $result = json_decode($response->getBody(), null, 512, JSON_THROW_ON_ERROR);

            if (property_exists($result, 'channels')) {
                $result->channels = get_object_vars($result->channels);
            }

            return $result;
        }, function (ConnectException $e) {
            throw new ApiErrorException($e->getMessage());
        });

        return $promise;
    }

    /**
     * Helper function to prepare batch trigger request. Takes the same                                                                                                                                               * parameters as the public batch trigger functions.
     *
     * @param array $batch [optional] An array of events to send
     * @param bool $already_encoded [optional]
     *
     * @return Request
     * @throws PusherException
     */
    public function make_batch_request(array $batch = [], bool $already_encoded = false): Request
    {
        foreach ($batch as $key => $event) {
            $this->validate_channel($event['channel']);
            if (isset($event['socket_id'])) {
                $this->validate_socket_id($event['socket_id']);
            }

            $data = $event['data'];
            if (!is_string($data)) {
                try {
                    $data = $already_encoded ? $data : json_encode($data, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new PusherException('Data encoding error.');
                }
            }

            if (PusherCrypto::is_encrypted_channel($event['channel'])) {
                $batch[$key]['data'] = $this->crypto->encrypt_payload($event['channel'], $data);
            } else {
                $batch[$key]['data'] = $data;
            }
        }

        $post_params = [];
        $post_params['batch'] = $batch;
        try {
            $post_value = json_encode($post_params, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new PusherException('Data encoding error.');
        }

        $query_params = [];
        $query_params['body_md5'] = md5($post_value);
        $path = $this->settings['base_path'] . '/batch_events';

        $signature = $this->sign($path, 'POST', $query_params);

        $this->log('trigger POST: {post_value}', compact('post_value'));

        $headers = [
            'Content-Type' => 'application/json',
            'X-Pusher-Library' => 'pusher-http-php ' . self::$VERSION
        ];

        $params = array_merge($signature, $query_params);
        $query_string = self::array_implode('=', '&', $params);
        $full_path = $path . "?" . $query_string;
        return new Request('POST', $full_path, $headers, $post_value);
    }

    /**
     * Trigger multiple events at the same time.
     *
     * @param array $batch [optional] An array of events to send
     * @param bool $already_encoded [optional]
     *
     * @return object
     * @throws ApiErrorException Throws ApiErrorException if the Channels HTTP API responds with an error
     * @throws GuzzleException
     * @throws PusherException
     */
    public function triggerBatch(array $batch = [], bool $already_encoded = false): object
    {
        $request = $this->make_batch_request($batch, $already_encoded);

        try {
            $response = $this->client->send($request, [
                'http_errors' => false,
                'base_uri' => $this->channels_url_prefix()
            ]);
        } catch (ConnectException $e) {
            throw new ApiErrorException($e->getMessage());
        }

        $status = $response->getStatusCode();

        if ($status !== 200) {
            $body = (string) $response->getBody();
            throw new ApiErrorException($body, $status);
        }

        try {
            $result = json_decode($response->getBody(), false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new PusherException('Data encoding error.');
        }

        if (property_exists($result, 'channels')) {
            $result->channels = get_object_vars($result->channels);
        }

        return $result;
    }

    /**
     * Asynchronously trigger multiple events at the same time.
     *
     * @param array $batch [optional] An array of events to send
     * @param bool $already_encoded [optional]
     *
     * @return PromiseInterface
     * @throws PusherException
     */
    public function triggerBatchAsync(array $batch = [], bool $already_encoded = false): PromiseInterface
    {
        $request = $this->make_batch_request($batch, $already_encoded);

        $promise = $this->client->sendAsync($request, [
            'http_errors' => false,
            'base_uri' => $this->channels_url_prefix()
        ])->then(function ($response) {
            $status = $response->getStatusCode();

            if ($status !== 200) {
                $body = (string) $response->getBody();
                throw new ApiErrorException($body, $status);
            }

            $result = json_decode($response->getBody(), false, 512, JSON_THROW_ON_ERROR);

            if (property_exists($result, 'channels')) {
                $result->channels = get_object_vars($result->channels);
            }

            return $result;
        }, function (ConnectException $e) {
            throw new ApiErrorException($e->getMessage());
        });

        return $promise;
    }

    /**
     * Fetch channel information for a specific channel.
     *
     * @param string $channel The name of the channel
     * @param array  $params  Additional parameters for the query e.g. $params = array( 'info' => 'connection_count' )
     *
     * @throws PusherException   If $channel is invalid
     * @throws ApiErrorException Throws ApiErrorException if the Channels HTTP API responds with an error
     * @throws GuzzleException
     *
     */
    public function getChannelInfo(string $channel, array $params = []): object
    {
        $this->validate_channel($channel);

        return $this->get('/channels/' . $channel, $params);
    }

    /**
     * @deprecated in favour of getChannelInfo
     */
    public function get_channel_info(string $channel, array $params = []): object
    {
        return $this->getChannelInfo($channel, $params);
    }

    /**
     * Fetch a list containing all channels.
     *
     * @param array $params Additional parameters for the query e.g. $params = array( 'info' => 'connection_count' )
     *
     * @throws ApiErrorException Throws ApiErrorException if the Channels HTTP API responds with an error
     * @throws GuzzleException
     *
     */
    public function getChannels(array $params = []): object
    {
        $result = $this->get('/channels', $params);

        $result->channels = get_object_vars($result->channels);

        return $result;
    }

    /**
     * @deprecated in favour of getChannels
     */
    public function get_channels(array $params = []): object
    {
        return $this->getChannels($params);
    }

    /**
     * Fetch user ids currently subscribed to a presence channel.
     *
     * @param string $channel The name of the channel
     *
     * @throws ApiErrorException Throws ApiErrorException if the Channels HTTP API responds with an error
     * @throws GuzzleException
     *
     */
    public function getPresenceUsers(string $channel): object
    {
        return $this->get('/channels/' . $channel . '/users');
    }

    /**
     * @deprecated in favour of getPresenceUsers
     */
    public function get_users_info(string $channel): object
    {
        return $this->getPresenceUsers($channel);
    }

    /**
     * GET arbitrary REST API resource using a synchronous http client.
     * All request signing is handled automatically.
     *
     * @param string $path        Path excluding /apps/APP_ID
     * @param array  $params      API params (see http://pusher.com/docs/rest_api)
     * @param bool   $associative When true, return the response body as an associative array, else return as an object
     *
     * @throws ApiErrorException Throws ApiErrorException if the Channels HTTP API responds with an error
     * @throws GuzzleException
     * @throws PusherException
     *
     * @return mixed See Pusher API docs
     */
    public function get(string $path, array $params = [], $associative = false)
    {
        $path = $this->settings['base_path'] . $path;

        $signature = $this->sign($path, 'GET', $params);

        $headers = [
            'Content-Type' => 'application/json',
            'X-Pusher-Library' => 'pusher-http-php ' . self::$VERSION
        ];

        $response = $this->client->get($path, [
            'query' => $signature,
            'http_errors' => false,
            'headers' => $headers,
            'base_uri' => $this->channels_url_prefix()
        ]);

        $status = $response->getStatusCode();

        if ($status !== 200) {
            $body = (string) $response->getBody();
            throw new ApiErrorException($body, $status);
        }

        try {
            $body = json_decode($response->getBody(), $associative, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new PusherException('Data decoding error.');
        }

        return $body;
    }

    /**
     * Creates a socket signature.
     *
     * @param string $channel
     * @param string $socket_id
     * @param string|null $custom_data
     *
     * @return string Json encoded authentication string.
     * @throws PusherException Throws exception if $channel is invalid or above or $socket_id is invalid
     */
    public function socketAuth(string $channel, string $socket_id, string $custom_data = null): string
    {
        $this->validate_channel($channel);
        $this->validate_socket_id($socket_id);

        if ($custom_data) {
            $signature = hash_hmac('sha256', $socket_id . ':' . $channel . ':' . $custom_data, $this->settings['secret'], false);
        } else {
            $signature = hash_hmac('sha256', $socket_id . ':' . $channel, $this->settings['secret'], false);
        }

        $signature = ['auth' => $this->settings['auth_key'] . ':' . $signature];
        // add the custom data if it has been supplied
        if ($custom_data) {
            $signature['channel_data'] = $custom_data;
        }

        if (PusherCrypto::is_encrypted_channel($channel)) {
            if (!is_null($this->crypto)) {
                $signature['shared_secret'] = base64_encode($this->crypto->generate_shared_secret($channel));
            } else {
                throw new PusherException('You must specify an encryption master key to authorize an encrypted channel');
            }
        }

        try {
            $response = json_encode($signature, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        } catch (\JsonException $e) {
            throw new PusherException('Data encoding error.');
        }

        return $response;
    }

    /**
     * @deprecated in favour of socketAuth
     */
    public function socket_auth(string $channel, string $socket_id, string $custom_data = null): string
    {
        return $this->socketAuth($channel, $socket_id, $custom_data);
    }

    /**
     * Creates a presence signature (an extension of socket signing).
     *
     * @param string $channel
     * @param string $socket_id
     * @param string $user_id
     * @param mixed $user_info
     *
     * @return string
     * @throws PusherException Throws exception if $channel is invalid or above or $socket_id is invalid
     */
    public function presenceAuth(string $channel, string $socket_id, string $user_id, $user_info = null): string
    {
        $user_data = ['user_id' => $user_id];
        if ($user_info) {
            $user_data['user_info'] = $user_info;
        }

        try {
            return $this->socket_auth($channel, $socket_id, json_encode($user_data, JSON_THROW_ON_ERROR));
        } catch (\JsonException $e) {
            throw new PusherException('Data encoding error.');
        }
    }

    /**
     * @deprecated in favour of presenceAuth
     */
    public function presence_auth(string $channel, string $socket_id, string $user_id, $user_info = null): string
    {
        return $this->presenceAuth($channel, $socket_id, $user_id, $user_info);
    }

    /**
     * Verify that a webhook actually came from Pusher, decrypts any encrypted events, and marshals them into a PHP object.
     *
     * @param array  $headers a array of headers from the request (for example, from getallheaders())
     * @param string $body    the body of the request (for example, from file_get_contents('php://input'))
     *
     * @throws PusherException
     *
     * @return Webhook marshalled object with the properties time_ms (an int) and events (an array of event objects)
     */
    public function webhook(array $headers, string $body): object
    {
        $this->ensure_valid_signature($headers, $body);

        $decoded_events = [];
        try {
            $decoded_json = json_decode($body, false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $this->log('Unable to decrypt webhook event payload.', null, LogLevel::WARNING);
            throw new PusherException('Data encoding error.');
        }

        foreach ($decoded_json->events as $key => $event) {
            if (PusherCrypto::is_encrypted_channel($event->channel)) {
                if (!is_null($this->crypto)) {
                    $decryptedEvent = $this->crypto->decrypt_event($event);

                    if ($decryptedEvent === false) {
                        $this->log('Unable to decrypt webhook event payload. Wrong key? Ignoring.', null, LogLevel::WARNING);
                        continue;
                    }
                    $decoded_events[] = $decryptedEvent;
                } else {
                    $this->log('Got an encrypted webhook event payload, but no master key specified. Ignoring.', null, LogLevel::WARNING);
                    continue;
                }
            } else {
                $decoded_events[] = $event;
            }
        }
        return new Webhook($decoded_json->time_ms, $decoded_events);
    }

    /**
     * Verify that a given Pusher Signature is valid.
     *
     * @param array  $headers an array of headers from the request (for example, from getallheaders())
     * @param string $body    the body of the request (for example, from file_get_contents('php://input'))
     *
     * @throws PusherException if signature is incorrect.
     */
    public function verifySignature(array $headers, string $body): void
    {
        $x_pusher_key = $headers['X-Pusher-Key'];
        $x_pusher_signature = $headers['X-Pusher-Signature'];
        if ($x_pusher_key === $this->settings['auth_key']) {
            $expected = hash_hmac('sha256', $body, $this->settings['secret']);
            if ($expected === $x_pusher_signature) {
                return;
            }
        }

        throw new PusherException(sprintf('Received WebHook with invalid signature: got %s.', $x_pusher_signature));
    }

    /**
     * @deprecated in favour of verifySignature
     */
    public function ensure_valid_signature(array $headers, string $body): void
    {
        $this->verifySignature($headers, $body);
    }
}
