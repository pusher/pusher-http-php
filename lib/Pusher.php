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
	* Trigger an event by providing event name and payload. 
	* Optionally provide a socket ID to exclude a client (most likely the sender).
	*
	* @param array $channel An array of channel names to publish the event on.
	* @param string $event
	* @param mixed $data Event data
	* @param int $socket_id [optional]
	* @param bool $debug [optional]
	* @return bool|string
	*/
	public function trigger( $channels, $event, $data, $socket_id = null, $debug = false, $already_encoded = false )
	{
		if( is_string( $channels ) === true ) {
			$this->log( '->trigger received string channel "' . $channels . '". Converting to array.' );
			$channels = array( $channels );
		}

		if( count( $channels ) > 100 ) {
			throw new PusherException('An event can be triggered on a maximum of 100 channels in a single call.');
		}

		$query_params = array();
		
		$s_url = $this->settings['url'] . '/events';		
		
		$data_encoded = $already_encoded ? $data : json_encode( $data );

		$post_params = array();
		$post_params[ 'name' ] = $event;
		$post_params[ 'data' ] = $data_encoded;
		$post_params[ 'channels' ] = $channels;

		if ( $socket_id !== null )
		{
			$post_params[ 'socket_id' ] = $socket_id;
		}

		$post_value = json_encode( $post_params );

		$query_params['body_md5'] = md5( $post_value );

		$ch = $this->create_curl( $s_url, 'POST', $query_params );

		$this->log( 'trigger POST: ' . $post_value );

		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $post_value );

		$response = $this->exec_curl( $ch );

		if ( $response[ 'status' ] == 200 && $debug == false )
		{
			return true;
		}
		elseif ( $debug == true || $this->settings['debug'] == true )
		{
			return $response;
		}
		else
		{
			return false;
		}

	}

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
