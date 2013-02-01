<?php

/* 
		Pusher PHP Library
	/////////////////////////////////
	PHP library for the Pusher API.

	See the README for usage information: https://github.com/pusher/pusher-php-server

	Copyright 2011, Squeeks. Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php

	Contributors:
		+ Paul44 (http://github.com/Paul44)
		+ Ben Pickles (http://github.com/benpickles)
		+ Mastercoding (http://www.mastercoding.nl)
		+ Alias14 (mali0037@gmail.com)
		+ Max Williams (max@pusher.com)
		+ Zack Kitzmiller (delicious@zackisamazing.com)
		+ Andrew Bender (igothelp@gmail.com)
		+ Phil Leggetter (phil@leggetter.co.uk)
*/

class PusherException extends Exception
{
}

class PusherInstance {
	
	private static $instance = null;
	private static $app_id	= '';
	private static $secret	= '';
	private static $api_key = '';
	
	private function __construct() { }
	private function __clone() { }
	
	public static function get_pusher()
	{
		if (self::$instance !== null) return self::$instance;

		self::$instance = new Pusher(
			self::$api_key, 
			self::$secret, 
			self::$app_id
		);

		return self::$instance;
	}
}

class Pusher
{
	public static $VERSION = '2.0.0';

	private $settings = array ();
	private $logger = null;

	/**
	* PHP5 Constructor. 
	* 
	* Initializes a new Pusher instance with key, secret , app ID and channel. 
	* You can optionally turn on debugging for all requests by setting debug to true.
	* 
	* @param string $auth_key
	* @param string $secret
	* @param int $app_id
	* @param bool $debug [optional]
	* @param string $host [optional]
	* @param int $port [optional]
	* @param int $timeout [optional]
	*/
	public function __construct( $auth_key, $secret, $app_id, $debug = false, $host = 'http://api.pusherapp.com', $port = '80', $timeout = 30 )
	{
		// Check compatibility, disable for speed improvement
		$this->check_compatibility();

		// Setup defaults
		$this->settings['server'] = $host;
		$this->settings['port']		= $port;
		$this->settings['auth_key'] = $auth_key;
		$this->settings['secret'] = $secret;
		$this->settings['app_id'] = $app_id;
		$this->settings['url']		= '/apps/' . $this->settings['app_id'];
		$this->settings['debug']	= $debug;
		$this->settings['timeout']	= $timeout;

	}

	/**
	 * Set a logger to be informed of interal log messages.
	 */
	public function set_logger( $logger ) {
		$this->logger = $logger;
	}

	/**
	 *
	 */
	private function log( $msg ) {
		if( is_null( $this->logger ) == false ) {
			$this->logger->log( 'Pusher: ' . $msg );
		}
	}

	/**
	* Check if the current PHP setup is sufficient to run this class
	*/
	private function check_compatibility()
	{
		if ( ! extension_loaded( 'curl' ) || ! extension_loaded( 'json' ) )
		{
			throw new PusherException('There is missing dependant extensions - please ensure both cURL and JSON modules are installed');
		}

		if ( ! in_array( 'sha256', hash_algos() ) )
		{
			throw new PusherException('SHA256 appears to be unsupported - make sure you have support for it, or upgrade your version of PHP.');
		}

	}
	
	/**
	 * Utility function used to create the curl object with common settings
	 */
	private function create_curl($s_url, $request_method = 'GET', $query_params = array() )
	{
		# Create the signed signature...
		$signed_query = Pusher::build_auth_query_string(
			$this->settings['auth_key'],
			$this->settings['secret'],
			$request_method,
			$s_url,
			$query_params);

		$full_url = $this->settings['server'] . ':' . $this->settings['port'] . $s_url . '?' . $signed_query;

		$this->log( 'curl_init( ' . $full_url . ' )' );
		
		# Set cURL opts and execute request
		$ch = curl_init();
		if ( $ch === false )
		{
			throw new PusherException('Could not initialise cURL!');
		}
		
		curl_setopt( $ch, CURLOPT_URL, $full_url );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array ( "Content-Type: application/json" ) );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_TIMEOUT, $this->settings['timeout'] );
		
		return $ch;
	}

	/**
	 * Utility function to execute curl and create capture response information.
	 */
	private function exec_curl( $ch ) {
		$response = array();

		$response[ 'body' ] = curl_exec( $ch );
		$response[ 'status' ] = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		$this->log( 'exec_curl response: ' . print_r( $response, true ) );

		curl_close( $ch );

		return $response;
	}
	
	/**
	 *	Build the required HMAC'd auth string
	 *
	 *	@param string $auth_key
	 *	@param string $auth_secret
	 *	@param string $request_path
	 *	@param array $query_params
	 *	@param string $auth_version [optional]
	 *	@param string $auth_timestamp [optional]
	 *	@return string
	 */
	public static function build_auth_query_string($auth_key, $auth_secret, $request_method, $request_path,
		$query_params = array(), $auth_version = '1.0', $auth_timestamp = null)
	{ 
		$params = array();
		$params['auth_key'] = $auth_key;
		$params['auth_timestamp'] = (is_null($auth_timestamp)?time() : $auth_timestamp);
		$params['auth_version'] = $auth_version;
		
		$params = array_merge($params, $query_params);
		ksort($params);
		
		$string_to_sign = "$request_method\n" . $request_path . "\n" . Pusher::array_implode( '=', '&', $params );

		$auth_signature = hash_hmac( 'sha256', $string_to_sign, $auth_secret, false );
		
		$params['auth_signature'] = $auth_signature;
		ksort($params);
		
		$auth_query_string = Pusher::array_implode( '=', '&', $params );
		
		return $auth_query_string;
	}
	
	/**
	 * Implode an array with the key and value pair giving
	 * a glue, a separator between pairs and the array
	 * to implode.
	 * @param string $glue The glue between key and value
	 * @param string $separator Separator between pairs
	 * @param array $array The array to implode
	 * @return string The imploded array
	 */
	public static function array_implode( $glue, $separator, $array ) {
			if ( ! is_array( $array ) ) return $array;
			$string = array();
			foreach ( $array as $key => $val ) {
					if ( is_array( $val ) )
							$val = implode( ',', $val );
					$string[] = "{$key}{$glue}{$val}";

			}		 
			return implode( $separator, $string );
	}

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
	  *	Fetch channel information for a specific channel.
	  *
	  * @param string $channel The name of the channel
	  * @param array $params Additional parameters for the query e.g. $params = array( 'info' => 'connection_count' )
	  *	@return object
	  */
	public function get_channel_info($channel, $params = array() )
	{
		$response = $this->get( '/channels/' . $channel, $params );
		
		if( $response[ 'status' ] == 200)
		{
			$response = json_decode( $response[ 'body' ] );
		}
		else
		{
			$response = false;
		}
		
		return $response;
	}
	
	/**
	 * Fetch a list containing all channels
	 * 
	 * @param array $params Additional parameters for the query e.g. $params = array( 'info' => 'connection_count' )
	 *
	 * @return array
	 */
	public function get_channels($params = array())
	{
		$response = $this->get( '/channels', $params );
		
		if( $response[ 'status' ] == 200)
		{
			$response = json_decode( $response[ 'body' ] );
			$response->channels = get_object_vars( $response->channels );
		}
		else
		{
			$response = false;
		}
		
		return $response;
	}

	/**
	 * GET arbitrary REST API resource using a synchronous http client.
   * All request signing is handled automatically.
   *  
   * @param string path Path excluding /apps/APP_ID
   * @param params array API params (see http://pusher.com/docs/rest_api)
   *
   * @return See Pusher API docs
	 */
	public function get( $path, $params = array() ) {
		$s_url = $this->settings['url'] . $path;	

		$ch = $this->create_curl( $s_url, 'GET', $params );

		$response = $this->exec_curl( $ch );
		
		if( $response[ 'status' ] == 200)
		{
			$response[ 'result' ] = json_decode( $response[ 'body' ], true );
		}
		else
		{
			$response = false;
		}
		
		return $response;
	}

	/**
	* Creates a socket signature
	* 
	* @param int $socket_id
	* @param string $custom_data
	* @return string
	*/
	public function socket_auth( $channel, $socket_id, $custom_data = false )
	{
		if($custom_data == true)
		{
			$signature = hash_hmac( 'sha256', $socket_id . ':' . $channel . ':' . $custom_data, $this->settings['secret'], false );
		}
		else
		{
			$signature = hash_hmac( 'sha256', $socket_id . ':' . $channel, $this->settings['secret'], false );
		}

		$signature = array ( 'auth' => $this->settings['auth_key'] . ':' . $signature );
		// add the custom data if it has been supplied
		if($custom_data){
			$signature['channel_data'] = $custom_data;
		}
		return json_encode( $signature );

	}

	/**
	* Creates a presence signature (an extension of socket signing)
	*
	* @param int $socket_id
	* @param string $user_id
	* @param mixed $user_info
	* @return string
	*/
	public function presence_auth( $channel, $socket_id, $user_id, $user_info = false )
	{

		$user_data = array( 'user_id' => $user_id );
		if($user_info == true)
		{
			$user_data['user_info'] = $user_info;
		}

		return $this->socket_auth($channel, $socket_id, json_encode($user_data) );
	}

}
