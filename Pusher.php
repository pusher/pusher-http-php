<?php

/* 
		Pusher PHP Library
	/////////////////////////////////
	This is a very simple PHP library to the Pusher API.

		$pusher = new Pusher(APIKEY, CHANNEL, [Debug: true/false, HOST, PORT]);
		$pusher->trigger('my_event', 'test_channel', [Debug: true/false]);

	Copyright 2010, Squeeks. Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php 
	
*/

class Pusher
{
	private $settings = array();

	public function __construct($api_key, $channel, $debug = false, $host = 'http://api.pusherapp.com', $port = '80')
	{
		
		// Check for dependent PHP extensions (JSON, cURL)
		if(!extension_loaded('curl') || !extension_loaded('json'))
		{
			die("There is missing dependant extensions - please ensure both cURL and JSON modules are installed");
		}

		$this->settings['server']  = $host;
		$this->settings['port']    = $port;
		$this->settings['api_key'] = $api_key;
		$this->settings['channel'] = $channel;
		$this->settings['url']     = $this->settings['server'].'/app/'.$this->settings['api_key'].'/channel/'.$this->settings['channel'];
		$this->settings['debug']   = $debug;

	}

	public function trigger($event, $data, $debug = false)
	{
		if($ch = curl_init())
		{
			$payload = array('event' => $event, 'data' => $data);

			curl_setopt($ch, CURLOPT_URL, $this->settings['url']);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

			$response = curl_exec($ch);
			curl_close($ch);

			if($response == 'OK')
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
