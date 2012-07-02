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

	class PusherPushTest extends PHPUnit_Framework_TestCase
	{

		protected function setUp()
		{
			if(PUSHERAPP_AUTHKEY == '' || PUSHERAPP_SECRET == '' || PUSHERAPP_APPID == '' )
			{
				$this->markTestSkipped('Please set the
					PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET and
					PUSHERAPP_APPID keys.');
			}
			else
			{
				$this->pusher = new Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, true);
			}
		}

		public function testObjectConstruct()
		{
			$this->assertNotNull($this->pusher, 'Created new Pusher object');
		}

		public function testStringPush()
		{
			$string_trigger = $this->pusher->trigger('test_channel', 'my_event', 'Test string');
			$this->assertTrue($string_trigger, 'Trigger with string payload');
		}

		public function testArrayPush()
		{
			$structure_trigger = $this->pusher->trigger('test_channel', 'my_event', array( 'test' => 1 ));
			$this->assertTrue($structure_trigger, 'Trigger with structured payload');
		}
	}

?>
