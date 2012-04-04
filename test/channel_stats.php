<?php

  if(file_exists('push.php') === true)
  {
    require_once('config.php');
  }
  else
  {
    define('PUSHERAPP_AUTHKEY', getenv('PUSHERAPP_AUTHKEY'));
    define('PUSHERAPP_SECRET' , getenv('PUSHERAPP_SECRET'));
    define('PUSHERAPP_APPID'  , getenv('PUSHERAPP_APPID'));
  }

  require_once('../lib/Pusher.php');
	
	class PusherChannelStatsTest extends PHPUnit_Framework_TestCase
	{

		protected function setUp()
		{
			$this->pusher = new Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, true);
		}

		public function testChannelStats()
		{
			$response = $this->pusher->get_channel_stats('channel-test');
			
			$this->assertObjectHasAttribute('occupied', $response, 'class has occupied attribute');
		}
		
		
		public function testChannelList()
		{
			$channels = $this->pusher->get_channels_list();
			
			$this->assertTrue( is_array($channels), 'channels is an array' );
		}
		
	}

?>
