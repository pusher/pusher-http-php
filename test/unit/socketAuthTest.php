<?php
	
	class PusherSocketAuthTest extends PHPUnit_Framework_TestCase
	{

		protected function setUp()
		{
			$this->pusher = new Pusher('thisisaauthkey', 'thisisasecret', 1, true); 
		}

		public function testObjectConstruct()
		{
			$this->assertNotNull($this->pusher, 'Created new Pusher object');
		}

		public function testSocketAuthKey()
		{
			$socket_auth = $this->pusher->socket_auth('testing_pusher-php', '1.1');
			$this->assertEquals($socket_auth, 
				'{"auth":"thisisaauthkey:751ccc12aeaa79d46f7c199bced5fa47527d3480b51fe61a0bd10438241bd52d"}',
				'Socket auth key valid');
		}

		/**
		 * @expectedException PusherException
		 */
		public function testInvalidSocketThrowsException()
		{
			$this->pusher->socket_auth('testing_pusher-php', 'invalid');
		}
	}

?>
