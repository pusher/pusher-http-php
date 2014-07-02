<?php

namespace pusher;

use pusher\Config;
use pusher\Client;

/**
 * This is the main class used to interact with the pusher API and related
 * constructs.
 */
class Pusher {

    public $config;

    /**
     * @throws pusher\ConfigurationError
     */
    public function __construct($config) {
        if (is_array($config) || is_string($config)) {
            $config = new Config($config);
            $config->validate();
        }
        $this->config = $config;
        $this->client = new Client($config);
    }

    public function keyPair() {
        return $this->config->firstKeyPair();
    }

    /**
     * Returns a JSON-encoded string that's valid for Pusher-js client
     * authentication.
     *
     * @param $socket_id string
     * @param $channel_name string
     * @param $channel_data array|null
     * @return string
     */
    public function authenticate($socket_id, $channel_name, $channel_data = null) {
        $kp = $this->keyPair();

        if (!is_null($channel_data)) {
            $channel_data = json_encode($channel_data);
        }

        $signature = $kp->authenticate($socket_id, $channel, $channel_data);

        $json = array('auth' => $kp->key . ':' . $signature);

        if (!is_null($channel_data)) {
            $json['channel_data'] = $channel_data;
        }
        return json_encode($json);
    }

    /**
     * Validates and decodes an incoming HTTP webhook request from Pusher and
     * returns the parsed JSON data.
     *
     * If the request is invalid the request is short-circuited and returns
     * a 401 Unauthorized response.
     *
     * @param $request array|null A $_REQUEST object or similar
     * @return mixed
     */
    public function webhook($request = null) {
        if (is_null($request)) {
            $request = $_REQUEST;
        }
        return new WebHook($request, $this);
    }

    /**
     * Triggers an event trough the API.
     *
     * @param $channels string|array A list of channels to send the event to
     * @param $event string name of the event
     * @param $data array data associated to the event
     * @param $socket_id string|null
     * @return array
     */
    public function trigger($channels, $event, $data, $socket_id = null) {
        if (is_string($channels)) {
            $channels = array($channels);
        } else if (count($channels) > 10) {
            throw new PusherException('An event can be triggered on a maximum of 10 channels in a single call.');
        }

        $data = json_encode($data);

        $body = array();
        $body['name'] = $event;
        $body['data'] = $data;
        $body['channels'] = $channels;

        if ($socket_id) {
            $body['socket_id'] = $socket_id;
        }

        return $this->client->post('events', $body);
    }

    /**
     * Request a list of occupied channels from the API
     *
     * GET /apps/[id]/channels
     *
     * @param $params array Hash of parameters for the API - see REST API docs
     * @return array See Pusher API docs
     */
    public function channels($params = array()) {
        return $this->client->get('/channels', $params);
    }

    /**
     * Request information about a channel from the API
     *
     * @param $channel_name string
     * @param $params array
     * @return array
     */
    public function channelInfo($channel_name, $params = array()) {
        return $this->client->get("/channels/$channel_name", $params);
    }

    /**
     * Request a list of users on a persence channel from the API
     *
     * @see http://pusher.com/docs/rest_api#method-get-users
     *
     * @param $channel_name string
     * @param $params array
     * @return array
     */
    public function presenceUsers($channel_name, $params = array()) {
        return $this->client->get("/channels/$channel_name/users", $params);
    }

    /**
     * @return boolean true if the channel is a presence channel.
     */
    private function is_presence($channel_name) {
        return strncmp("presence-", $channel_name, 0) == 0;
    }

}
