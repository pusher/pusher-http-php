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

    define('PUSHERAPP_HOST', 'http://api.pusherapp.com');
  }

  require_once('../lib/Pusher.php');
	
	class PusherChannelQueryTest extends PHPUnit_Framework_TestCase
	{

		protected function setUp()
		{
			$this->pusher = new Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, true, PUSHERAPP_HOST);
		}

		public function testChannelInfo()
		{
			$response = $this->pusher->get_channel_info('channel-test');
			
			//print_r( $response );
			
			$this->assertObjectHasAttribute('occupied', $response, 'class has occupied attribute');
		}
		
		public function testChannelList()
		{
			$result = $this->pusher->get_channels();
			$channels = $result->channels;
			
			 print_r( $channels );
			
			foreach( $channels as $channel_name => $channel_info ) {
        echo( "channel_name: $channel_name\n");
        echo( 'channel_info: ' );
        print_r( $channel_info );
        echo( "\n\n");
      }
			
			$this->assertTrue( is_array($channels), 'channels is an array' );
		}
		
		public function testFilterByPrefixNoChannels()
		{
			$options = array(
				'filter_by_prefix' => '__fish'
			);
		  $result = $this->pusher->get_channels( $options );

print_r( $result );

		  $channels = $result->channels;
		  
		  print_r( $channels );
		  
			$this->assertTrue( is_array($channels), 'channels is an array' );
			$this->assertEquals( 0, count( $channels ), 'should be an empty array' );
		}

		public function testFilterByPrefixOneChannel()
		{
			$options = array(
				'filter_by_prefix' => 'test_'
			);
		  $result = $this->pusher->get_channels( $options );

print_r( $result );

		  $channels = $result->channels;
		  
		  print_r( $channels );
		  
			$this->assertEquals( 1, count( $channels ), 'channels have a single test-channel present. For this test to pass you must have your API Access setting open for the application you are testing against' );
		}


		public function test_providing_info_parameter_with_prefix_query_fails_for_public_channel()
		{
			$options = array(
				'filter_by_prefix' => 'test_',
				'info' => 'user_count'
			);
		  $result = $this->pusher->get_channels( $options );
		  
			$this->assertFalse( $result, 'query should fail' );
		}
		
	}

?>
