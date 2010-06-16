<?php

/* 
    Pusher PHP Library
  /////////////////////////////////
  This was a very simple PHP library to the Pusher API.

    $pusher = new Pusher(APIKEY, SECRET, APP_ID, CHANNEL, [Debug: true/false, HOST, PORT]);
    $pusher->trigger('my_event', 'test_channel', [socket_id, Debug: true/false]);
    $pusher->socket_auth('socket_id');

  Copyright 2010, Squeeks. Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
  
  Modified 14-06-10 by Mastercoding (http://www.mastercoding.nl)
  	- fixed socket_auth function
  	- added timeout param to constructor, channel name is optional now. Can also be set when triggering an event
  	- moved compatibility check to a seperate function to allow for easy disabling (also improved the checking)
  	- added socket_id and channel to trigger function
  
*/
class Pusher {

  private $settings = array ();

  /**
   * PHP5 Constructor. 
   * 
   * Initializes a new Pusher instance with key, secret , app id and channel. 
   * You can optionally turn on debugging for all requests by setting debug to true.
   * 
   * @param string $auth_key
   * @param string $secret
   * @param int $app_id
   * @param string $channel [optional]
   * @param bool $debug [optional]
   * @param string $host [optional]
   * @param int $port [optional]
   * @param int $timeout [optional]
   */
  public function __construct( $auth_key, $secret, $app_id, $channel = '', $debug = false, $host = 'http://api.pusherapp.com', $port = '80', $timeout = 30 ) {

    // check compatibility, disable for speed improvement
    $this->checkCompatibility();
    
    // Setup defaults
    $this->settings['server'] = $host;
    $this->settings['port'] = $port;
    $this->settings['auth_key'] = $auth_key;
    $this->settings['secret'] = $secret;
    $this->settings['app_id'] = $app_id;
    $this->settings['channel'] = $channel;
    $this->settings['url'] = '/apps/' . $this->settings['app_id'];
    $this->settings['debug'] = $debug;
    $this->settings['timeout'] = $timeout;
  
  }

  /**
   * Check if the current PHP setup is sufficient to run this class
   */
  private function checkCompatibility() {

    // Check for dependent PHP extensions (JSON, cURL)
    if ( ! extension_loaded( 'curl' ) || ! extension_loaded( 'json' ) )
      die( 'There is missing dependant extensions - please ensure both cURL and JSON modules are installed' );
      
    # supports sha256?
    if ( ! in_array( 'sha256', hash_algos() ) )
      die( 'SHA256 appears to be unsupported - make sure you have support for it, or upgrade your version of PHP.' );
  
  }

  /**
   * Trigger an event by providing event name and payload. 
   * Optionally provide a socket id to exclude a client (most likely the sender).
   * 
   * @param string $event
   * @param mixed $payload
   * @param int $socket_id [optional]
   * @param string $channel [optional]
   * @param bool $debug [optional]
   * @return bool|string
   */
  public function trigger( $event, $payload, $socket_id = null, $channel = '', $debug = false ) {

    # check if we can initialize a curl connection
    $ch = curl_init();
    if ( $ch === false )
      die( 'Could not initialise cURL!' );
      
    # add channel to url..
    $sURL = $this->settings['url'] . '/channels/' . ($channel != '' ? $channel : $this->settings['channel']) . '/events';
    
    # build request
    $signature = "POST\n" . $sURL . "\n";
    $payload_encoded = json_encode( $payload );
    $query = "auth_key=" . $this->settings['auth_key'] . "&auth_timestamp=" . time() . "&auth_version=1.0&body_md5=" . md5( $payload_encoded ) . "&name=" . $event;
    
    # socket id set?
    if ( $socket_id !== null )
      $query .= "&socket_id=" . $socket_id;
      
    # create signed signature...
    $auth_signature = hash_hmac( 'sha256', $signature . $query, $this->settings['secret'], false );
    $signed_query = $query . "&auth_signature=" . $auth_signature;
    $full_url = $this->settings['server'] . ':' . $this->settings['port'] . $sURL . '?' . $signed_query;
    
    # set curl opts and execute request
    curl_setopt( $ch, CURLOPT_URL, $full_url );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array ( "Content-Type: application/json" ) );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt( $ch, CURLOPT_POST, 1 );
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload_encoded );
    curl_setopt( $ch, CURLOPT_TIMEOUT, $this->settings['timeout'] );
    $response = curl_exec( $ch );
    curl_close( $ch );
    
    if ( $response == "202 ACCEPTED\n" && $debug == false )
      return true;
    elseif ( $debug == true || $this->settings['debug'] == true )
      return $response;
    else
      return false;
  
  }

  /**
   * Creates a socket signature
   * 
   * @param int $socket_id
   * @return string
   */
  public function socket_auth( $socket_id ) {

    # socket_id <> channel swapped, wrong in documentation
    $signature = hash_hmac( 'sha256', $socket_id . ':' . $this->settings['channel'], $this->settings['secret'], false );
    $signature = array ( 'auth' => $this->settings['auth_key'] . ':' . $signature );
    return json_encode( $signature );
    
  }

}

?>