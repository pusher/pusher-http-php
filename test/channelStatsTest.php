<?php

  if(file_exists('config.php') === true)
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
			
			//print_r( $response );
			
			$this->assertObjectHasAttribute('occupied', $response, 'class has occupied attribute');
		}
		
		public function testChannelList()
		{
			$result = $this->pusher->get_channels();
			$channels = $result->channels;
			
			print_r( $channels );
			
			foreach( $channels as $channel ) {
        //echo( $channel->name );
      }
			
			$this->assertTrue( is_array($channels), 'channels is an array' );
		}
		
		public function testPresenceChannelsList()
		{
		  $result = $this->pusher->get_presence_channels();
		  $presence_channels = $result->channels;
		  
		  print_r( $presence_channels );
		  
		  foreach( $presence_channels as $name => $stats ) {
        //echo( $name );
        //print_r( $stats );
        // echo( $stats->user_count );
      }
		  
			$this->assertTrue( is_array($presence_channels), 'channels is an array' );
		}
		
	}

?>
