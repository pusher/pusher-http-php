<?php
	
	class PusherTriggerUnitTest extends PHPUnit_Framework_TestCase
	{

		protected function setUp()
		{
			$this->pusher = new Pusher('thisisaauthkey', 'thisisasecret', 1, true);
			$this->eventName = 'test_event';
			$this->data = array();
		}
    
    /**
		 * @expectedException PusherException
		 */
		public function testTrailingColonChannelThrowsException()
		{
			$this->pusher->trigger('test_channel:', $this->eventName, $this->data);
		}

		/**
		 * @expectedException PusherException
		 */
		public function testLeadingColonChannelThrowsException()
		{
			$this->pusher->trigger(':test_channel', $this->eventName, $this->data);
		}

		/**
		 * @expectedException PusherException
		 */
		public function testLeadingColonNLChannelThrowsException()
		{
			$this->pusher->trigger(':\ntest_channel', $this->eventName, $this->data);
		}

		/**
		 * @expectedException PusherException
		 */
		public function testTrailingColonNLChannelThrowsException()
		{
			$this->pusher->trigger('test_channel\n:', $this->eventName, $this->data);
		}
		
		/**
		* @expectedException PusherException
		*/
		public function testChannelArrayThrowsException()
		{
			$this->pusher->trigger(['this_one_is_okay', 'test_channel\n:'], $this->eventName, $this->data);
		}
    
  }
