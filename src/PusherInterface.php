<?php

namespace Pusher;

interface PusherInterface
{
    /**
     * Fetch the settings.
     *
     * @return array
     */
    public function getSettings();

    /**
     * Set a logger to be informed of internal log messages.
     *
     * @deprecated Use the PSR-3 compliant Pusher::setLogger() instead. This method will be removed in the next breaking release.
     *
     * @param object $logger A object with a public function log($message) method
     *
     * @return void
     */
    public function set_logger($logger);

    /**
     * Trigger an event by providing event name and payload.
     * Optionally provide a socket ID to exclude a client (most likely the sender).
     *
     * @param array|string $channels        A channel name or an array of channel names to publish the event on.
     * @param string       $event
     * @param mixed        $data            Event data
     * @param string|null  $socket_id       [optional]
     * @param bool         $already_encoded [optional]
     *
     * @throws PusherException   Throws exception if $channels is an array of size 101 or above or $socket_id is invalid
     * @throws ApiErrorException Throws ApiErrorException if the Channels HTTP API responds with an error
     *
     * @return object
     */
    public function trigger($channels, $event, $data, $socket_id = null, $already_encoded = false);

    /**
     * Trigger multiple events at the same time.
     *
     * @param array $batch           [optional] An array of events to send
     * @param bool  $already_encoded [optional]
     *
     * @throws PusherException   Throws exception if curl wasn't initialized correctly
     * @throws ApiErrorException Throws ApiErrorException if the Channels HTTP API responds with an error
     *
     * @return object
     */
    public function triggerBatch($batch = array(), $already_encoded = false);

    /**
     * Fetch channel information for a specific channel.
     *
     * @param string $channel The name of the channel
     * @param array  $params  Additional parameters for the query e.g. $params = array( 'info' => 'connection_count' )
     *
     * @throws PusherException   If $channel is invalid or if curl wasn't initialized correctly
     * @throws ApiErrorException Throws ApiErrorException if the Channels HTTP API responds with an error
     *
     * @return object
     */
    public function get_channel_info($channel, $params = array());

    /**
     * Fetch a list containing all channels.
     *
     * @param array $params Additional parameters for the query e.g. $params = array( 'info' => 'connection_count' )
     *
     * @throws PusherException   Throws exception if curl wasn't initialized correctly
     * @throws ApiErrorException Throws ApiErrorException if the Channels HTTP API responds with an error
     *
     * @return object
     */
    public function get_channels($params = array());

    /**
     * Fetch user ids currently subscribed to a presence channel.
     *
     * @param string $channel The name of the channel
     *
     * @throws PusherException   Throws exception if curl wasn't initialized correctly
     * @throws ApiErrorException Throws ApiErrorException if the Channels HTTP API responds with an error
     *
     * @return object
     */
    public function get_users_info($channel);

    /**
     * GET arbitrary REST API resource using a synchronous http client.
     * All request signing is handled automatically.
     *
     * @param string $path   Path excluding /apps/APP_ID
     * @param array  $params API params (see http://pusher.com/docs/rest_api)
     * @param bool   $associative When true, return the response body as an associative array, else return as an object
     *
     * @throws PusherException   Throws exception if curl wasn't initialized correctly
     * @throws ApiErrorException Throws ApiErrorException if the Channels HTTP API responds with an error
     *
     * @return mixed See Pusher API docs
     */
    public function get($path, $params = array());

    /**
     * Creates a socket signature.
     *
     * @param string $channel
     * @param string $socket_id
     * @param string $custom_data
     *
     * @throws PusherException Throws exception if $channel is invalid or above or $socket_id is invalid
     *
     * @return string Json encoded authentication string.
     */
    public function socket_auth($channel, $socket_id, $custom_data = null);

    /**
     * Creates a presence signature (an extension of socket signing).
     *
     * @param string $channel
     * @param string $socket_id
     * @param string $user_id
     * @param mixed  $user_info
     *
     * @throws PusherException Throws exception if $channel is invalid or above or $socket_id is invalid
     *
     * @return string
     */
    public function presence_auth($channel, $socket_id, $user_id, $user_info = null);

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
    public function webhook($headers, $body);

    /**
     * Verify that a given Pusher Signature is valid.
     *
     * @param array  $headers an array of headers from the request (for example, from getallheaders())
     * @param string $body    the body of the request (for example, from file_get_contents('php://input'))
     *
     * @throws PusherException if signature is inccorrect.
     */
    public function ensure_valid_signature($headers, $body);
}
