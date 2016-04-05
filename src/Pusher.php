<?php

namespace Pusher;

use Pusher\Exception\ConfigurationException;
use Pusher\Http\Client;

/**
 * Main class used to interact with the pusher API and related constructs.
 */
class Pusher
{
    /**
     * The configuration object.
     *
     * @var Config
     */
    public $config;

    /**
     * The api client instance.
     *
     * @var Http\Client
     */
    public $httpClient;

    /**
     * Entry point of the api. Simply instantiates the library Config object.
     *
     * @param string $appId
     * @param string $key
     * @param string $secret
     * @param array $options
     *
     * @return void
     */
    public function __construct($appId, $key = null, $secret = null, $options = array())
    {
        if ($appId instanceof Config) {
            $this->config = $appId;
        } else {
            if (!is_string($key) && !is_string($secret)) {
                throw new ConfigurationException('Missing app key and secret.');
            }

            $options = array_merge($options, array(
                'app_id' => $appId,
                'keys' => array(
                    $key => $secret,
                ),
            ));

            $this->config = new Config($options);
        }

        $this->httpClient = new Client($this->config);
    }

    /**
     * @return KeyPair
     */
    public function keyPair()
    {
        return $this->config->firstKeyPair();
    }

    /**
     * Returns a JSON-encoded string that is valid for Pusher-js client
     * authentication.
     *
     * @param $socket_id string
     * @param $channel_name string
     * @param $channel_data array|null
     *
     * @return string
     */
    public function authenticate($socket_id, $channel_name, $channel_data = null)
    {
        $kp = $this->keyPair();

        if (!is_null($channel_data)) {
            $channel_data = json_encode($channel_data);
        }

        $signature = $kp->authenticate($socket_id, $channel_name, $channel_data);

        $json = array('auth' => $kp->key.':'.$signature);

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
     *
     * @return pusher\WebHook
     */
    public function webhook($server = null, $body_file = 'php://input')
    {
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
     * @param $socketId string|null
     *
     * @throws \Exception\Exception on invalid responses
     *
     * @return array
     */
    public function trigger($channels, $event, $data, $socketId = null)
    {
        $channels = (array) $channels;

        if (count($channels) > 10) {
            throw new Exception\Exception('An event can be triggered on a maximum of 10 channels in a single call.');
        }

        $data = json_encode($data);

        $body = array();
        $body['name'] = $event;
        $body['data'] = $data;
        $body['channels'] = $channels;

        if ($socketId) {
            $body['socket_id'] = $socketId;
        }

        return $this->httpClient->post('events', $body);
    }

    /**
     * Request a list of occupied channels from the API.
     *
     * GET /apps/[id]/channels
     *
     * @param $params array Hash of parameters for the API - see HTTP API docs
     *
     * @throws Exception\HttpException on invalid responses
     *
     * @return array See Pusher API docs
     */
    public function channels($params = array())
    {
        return $this->httpClient->get('/channels', $params);
    }

    /**
     * Request information about a channel from the API.
     *
     * @param $channel_name string
     * @param $params array
     *
     * @throws Exception\HttpException on invalid responses
     *
     * @return array
     */
    public function channelInfo($channel_name, $params = array())
    {
        return $this->httpClient->get("/channels/$channel_name", $params);
    }

    /**
     * Request a list of users on a persence channel from the API.
     *
     * @see http://pusher.com/docs/rest_api#method-get-users
     *
     * @param $channel_name string
     * @param $params array
     *
     * @throws Exception\HttpException on invalid responses
     *
     * @return array
     */
    public function presenceUsers($channel_name, $params = array())
    {
        return $this->httpClient->get("/channels/$channel_name/users", $params);
    }
}
