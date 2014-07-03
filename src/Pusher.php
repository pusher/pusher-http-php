<?php

namespace pusher;

use pusher\Config;
use pusher\Client;

/**
 * This is the main class used to interact with the pusher API and related
 * constructs.
 */
class Pusher {

    /**
     * @var pusher\Config
     */
    public $config;

    /**
     * @throws pusher\ConfigurationError
     */
    public function __construct($config) {
        $config = Config::ensure($config);
        $this->config = $config;
        $this->client = new Client($config);
    }

    /**
     * @return KeyPair
     */
    public function keyPair() {
        return $this->config->firstKeyPair();
    }

    /**
     * Returns a JSON-encoded string that is valid for Pusher-js client
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

        $signature = $kp->authenticate($socket_id, $channel_name, $channel_data);

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
     * @param $server array|null defaults to $_SERVER if null
     * @param $body_file string where to read the body from
     * @return pusher\WebHook
     */
    public function webhook($server = null, $body_file = 'php://input') {
        if (is_null($server)) {
            $server = $_SERVER;
        }
        $api_key = $server['HTTP_X_PUSHER_KEY'];
        $signature = $server['HTTP_X_PUSHER_SIGNATURE'];
        return new WebHook($this->config, $api_key, $signature, $body_file);
    }

    /**
     * Triggers an event trough the API.
     *
     * @param $channels string|array A list of channels to send the event to
     * @param $event string name of the event
     * @param $data array data associated to the event
     * @param $socket_id string|null
     * @throws Exception\HTTPError on invalid responses
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
     * @throws Exception\HTTPError on invalid responses
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
     * @throws Exception\HTTPError on invalid responses
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
     * @throws Exception\HTTPError on invalid responses
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
