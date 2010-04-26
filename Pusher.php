<?php

/* 
		Pusher PHP Library
	/////////////////////////////////
	This was a very simple PHP library to the Pusher API.

		$pusher = new Pusher(APIKEY, SECRET, APP_ID, CHANNEL, [Debug: true/false, HOST, PORT]);
		$pusher->trigger('my_event', 'test_channel', [Debug: true/false]);

	Copyright 2010, Squeeks. Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
	
*/

class Pusher
{
	private $settings = array();

	public function __construct($auth_key, $secret, $app_id, $channel, $debug = false, $host = 'http://api.pusherapp.com', $port = '80')
	{
		// Check for dependent PHP extensions (JSON, cURL)
		if(!extension_loaded('curl') || !extension_loaded('json'))
		{
			die("There is missing dependant extensions - please ensure both cURL and JSON modules are installed");
		}

		// Check to see if we do support MD5 and SHA256 - older PHP5 setups and locked down hosts may not support the latter.
		foreach(hash_algos() as $algo)
		{
			if($algo == 'sha256') { $has_sha256 = true; }
			if($algo == 'md5')    { $has_md5 = true; }
		}

		if(!isset($has_sha256) || !isset($has_md5))
		{
			die("Either MD5 and SHA256 appears to be unsupported - make sure you have support for it, or upgrade your version of PHP.");
		}

		// Setup defaults
		$this->settings['server']   = $host;
		$this->settings['port']     = $port;
		$this->settings['auth_key'] = $auth_key;
		$this->settings['secret']   = $secret;
		$this->settings['app_id']   = $app_id; 
		$this->settings['channel']  = $channel;
		$this->settings['url']      = '/apps/'.$this->settings['app_id'].'/channels/'.$this->settings['channel'].'/events';
		$this->settings['debug']    = $debug;

	}


	public function trigger($event, $payload, $debug = false)
	{
		if($ch = curl_init())
		{
			$time 		= time();
			$signature      = "POST\n".$this->settings['url']."\n";
			$query          = "auth_key=".$this->settings['auth_key']. "&auth_timestamp=".$time."&auth_version=1.0&body_md5=".md5(json_encode($payload))."&name=".$event;
			$auth_signature = urlencode( base64_encode(hash_hmac('sha256', $signature.$query, $this->settings['secret'], true)) );
			$signed_query   = $query."&auth_signature=".$auth_signature;
			$full_url = $this->settings['server'].':'.$this->settings['port'].$this->settings['url'].'?'.$signed_query;

			curl_setopt($ch, CURLOPT_URL, $full_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

			$response = curl_exec($ch);

			curl_close($ch);

			if($response == "202 ACCEPTED\n" && $debug == false)
			{ 
			 	return true;
			}
			elseif($debug == true || $this->settings['debug'] == true)
			{
				return $response;
			}
			else
			{
				return false;
			}

		}
		else
		{
			die("Could not initialise cURL!");
		}
		
	}

}

?>
