<?php namespace Pusher;

/**
 * Pusher PHP Library
 * PHP library for the Pusher API.
 * See the README for usage information: https://github.com/pusher/pusher-php-server
 *
 * @package     Pusher
 * @copyright   2011,   Squeeks
 * @licence     http://www.opensource.org/licenses/mit-license.php  MIT
 * @author      Paul44          <http://github.com/Paul44>
 * @author      Ben Pickles     <http://github.com/benpickles>
 * @author      MasterCoding    <http://www.mastercoding.nl>
 * @author      Alias14         <mali0037@gmail.com>
 * @author      Max Williams    <max@pusher.com>
 * @author      Zack Kitzmiller <delicious@zackisamazing.com>
 * @author      Andrew Bender   <igothelp@gmail.com>
 * @author      Phil Leggetter  <phil@leggetter.co.uk>
 * @author      Mohammad Gufran <me@gufran.me>
 */

use Pusher\Interfaces\LoggerInterface;
use Pusher\Exceptions\PusherException;

class Pusher
{
    /**
     * Current version of Pusher library
     */
    const VERSION = '3.0.0';

    /**
     * @var bool
     */
    protected $debug;

    /**
     * @var string
     */
    protected $secret;

    /**
     * @var string
     */
    protected $authKey;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var int
     */
    protected $appId;

    /**
     * @var LoggerInterface
     */
    private $logger = null;

    /**
     * @var Client
     */
    private $client;

    /**
     * Initializes a new Pusher instance with key, secret, app ID and channel.
     * You can optionally supply an array of configuration to alter functionality.
     * Supported keys in array:
     * <code>
     *     array(
     *       'debug'    => false,                   // Enable or disable debugging
     *       'host'     => 'api.pusherapp.com',     // Change the host server URL
     *       'secured'  => true,                    // Force https instead of http
     *       'port'     => 80,                      // Port to connect
     *       'timeout'  => 30                       // Timeout in seconds
     *     )
     * </code>
     *
     * @param string          $authKey
     * @param string          $secret
     * @param string          $appId
     * @param array           $config
     * @param LoggerInterface $logger [optional]
     * @param Client          $client [Optional]
     */
    public function __construct($authKey, $secret, $appId, array $config = array(), LoggerInterface $logger = null, Client $client = null)
    {
        $config = $this->resolveConfig($config);

        $this->authKey = $authKey;
        $this->secret = $secret;
        $this->appId = $appId;
        $this->logger = $logger;
        $this->debug = $config['debug'];
        $this->url = '/apps/' . $appId;

        $protocol = $config['secured'] ? 'https' : 'http';

        if(is_null($client))
        {
            $this->client = new Client($protocol . '://' . $config['host'], $config['port'], $authKey, $secret, $config['timeout']);
        }
        else
        {
            $this->client = $client;
        }
    }

    /**
     * Set a logger to be informed of internal log messages.
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Trigger an event by providing event name and payload.
     * Optionally provide a socket ID to exclude a client (most likely the sender).
     *
     * @param string|array $channels
     * @param string       $event
     * @param string|array $data
     * @param string $socketId
     * @param bool         $alreadyEncoded
     * @return bool
     * @throws Exceptions\PusherException
     */
    public function trigger($channels, $event, $data, $socketId = null, $alreadyEncoded = false)
    {
        if (is_string($channels))
        {
            $this->log('Pusher::trigger() received string channel [' . $channels . ']. Converting to array.');
            $channels = [$channels];
        }

        if (count($channels) > 100)
        {
            throw new PusherException('An event can be triggered on a maximum of 100 channels in a single call.');
        }

        $url = $this->url . '/events';
        $data = $alreadyEncoded ? $data : json_encode($data);

        $postParams = $this->getPostParams($channels, $event, $data, $socketId);

        $queryParams = $this->getQueryParams($postParams);

        $this->log('Pusher::trigger() making POST request with [Post Parameters: ' . $postParams . '] and [Query Params: (array)' . json_encode($queryParams) . ']');

        $response = $this->client->post($url, $queryParams, $postParams);

        $this->log('Pusher::trigger() received response code [' . $response['status'] . '].');

        return ($response['status'] == 200);
    }

    /**
     * Fetch channel information for a specific channel.
     *
     * @param string $channel
     * @param array  $params
     * @return object
     */
    public function getChannelInfo($channel, array $params = array())
    {
        $response = $this->get('/channels/' . $channel, $params);
        $this->log('Pusher::getChannelInfo() retrieving channel info for channel [' . $channel . ']');

        if ($response['status'] == 200)
        {
            $response = json_decode($response['body']);
        }
        else
        {
            $this->log('Pusher::getChannelInfo() Error occurred [Response: ' . $response . ']');
            $response = false;
        }

        return $response;
    }

    /**
     * Fetch a list containing all channels
     *
     * @param array $params
     * @return array
     */
    public function getChannels(array $params = array())
    {
        $this->log('Pusher::getChannels() retrieving list of channels [Parameters: ' . json_encode($params). ']');
        $response = $this->get('/channels', $params);

        if ($response['status'] == 200)
        {
            $response = json_decode($response['body']);
            $response->channels = get_object_vars($response->channels);
        }
        else
        {
            $this->log('Pusher::getChannels() Error occurred [Response: ' . json_encode($response['body']). ']');
            $response = false;
        }

        return $response;
    }

    /**
     * GET arbitrary REST API resource using a synchronous http client.
     * All request signing is handled automatically.
     *
     * @param string $path
     * @param array $params
     * @return array | false
     */
    public function get($path, array $params = array())
    {
        $this->log('Pusher::get() Fetching resource [' . $path . '] with parameters [' . json_encode($params) . ']');

        $url = $this->url . $path;
        $response = $this->client->get($url, $params);

        if ($response['status'] == 200)
        {
            $response['result'] = json_decode($response['body'], true);
        }
        else
        {
            $this->log('Pusher::get() Error occurred [Response: ' . $response['body'] . ']');
            $response = false;
        }

        return $response;
    }

    /**
     * create a socket signature
     *
     * @param      $channel
     * @param      $socketId
     * @param string $customData
     * @return string
     */
    public function socketAuth($channel, $socketId, $customData = null)
    {
        $this->log('Pusher::socketAuth() creating socket authorization hash for channel [' . $channel . ']');

        if ($customData)
        {
            $signature = hash_hmac('sha256', $socketId . ':' . $channel . ':' . $customData, $this->secret, false);
        }
        else
        {
            $signature = hash_hmac('sha256', $socketId . ':' . $channel, $this->secret, false);
        }

        $signature = array('auth' => $this->authKey . ':' . $signature);

        if ($customData)
        {
            $signature['channel_data'] = $customData;
        }

        return json_encode($signature);
    }

    /**
     * Creates a presence signature (an extension of socket signing)
     *
     * @param string   $channel
     * @param int $socketId
     * @param string $userId
     * @param mixed $userInfo
     * @return string
     */
    public function presenceAuth($channel, $socketId, $userId, $userInfo = null)
    {
        $userData = array('user_id' => $userId);

        if($userInfo)
        {
            $userData['user_info'] = $userInfo;
        }

        return $this->socketAuth($channel, $socketId, json_encode($userData));
    }

    /**
     * @param $channels
     * @param $event
     * @param $data
     * @param $socket_id
     * @return string
     */
    private function getPostParams($channels, $event, $data, $socket_id)
    {
        $postParams['name'] = $event;
        $postParams['data'] = $data;
        $postParams['channels'] = $channels;

        if ($socket_id !== null)
        {
            $postParams['socket_id'] = $socket_id;
        }

        return json_encode($postParams);
    }

    /**
     * @param $data
     * @return array
     */
    private function getQueryParams($data)
    {
        return array('body_md5' =>  md5($data));
    }

    /**
     * Log a message to registered logger
     *
     * @param string $msg
     */
    private function log($msg)
    {
        if ( ! is_null($this->logger))
        {
            $this->logger->log('Pusher: ' . $msg);
        }
    }

    /**
     * @param $config
     * @return array
     */
    private function resolveConfig($config)
    {
        $defaultConfig = array(
            'debug'    => false,
            'host'     => 'api.pusherapp.com',
            'secured'  => true,
            'port'     => 80,
            'timeout'  => 30
        );

        return $defaultConfig + $config;
    }
}
