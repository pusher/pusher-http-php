<?php

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
			$options = array(
				'host' => PUSHERAPP_HOST,
				'debug' => true
			);
			$this->pusher = new Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, $options );
			$this->pusher->set_logger( new TestLogger() );
		}
	}

	public function testStringPush()
	{
		$triggerResult = $this->pusher->trigger('test_channel', 'my_event', 'Test string');
		$this->assertInstanceOf('PusherTriggerResult', $triggerResult, 'Trigger with string payload');
	}

	public function testArrayPush()
	{
		$triggerResult = $this->pusher->trigger('test_channel', 'my_event', array( 'test' => 1 ));
		$this->assertInstanceOf('PusherTriggerResult', $triggerResult, 'Trigger with structured payload');
	}

	public function testTriggeringOnSingleChannelReturnsEventId() {
		$triggerResult = $this->pusher->trigger('ch1', 'my_event', array( 'test' => 1 ));

		print_r($triggerResult);

		$this->assertNotNull($triggerResult);
		$this->assertNotNull($triggerResult->eventIds['ch1']);
	}

	public function testTriggeringOnMultipleChannelsReturnsEventIds() {
		$triggerResult = $this->pusher->trigger(['ch1', 'ch2', 'ch3'], 'my_event', array( 'test' => 1 ));

		$this->assertNotNull($triggerResult);
		$this->assertNotNull($triggerResult->eventIds['ch1']);
		$this->assertNotNull($triggerResult->eventIds['ch2']);
		$this->assertNotNull($triggerResult->eventIds['ch3']);
	}
	
	public function testNullSocketID()
	{
		// Check this does not throw an exception
		$triggerResult = $this->pusher->trigger('test_channel', 'my_event', array('fish' => 'pie'), null);
		$this->assertInstanceOf('PusherTriggerResult', $triggerResult, 'Trigger with null $socket_id');
	}

	public function testEncryptedPush()
	{
		$options = array(
			'encrypted' => true,
			'host' => PUSHERAPP_HOST
		);
		$pusher = new Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, $options);
		$pusher->set_logger( new TestLogger() );

		$triggerResult = $pusher->trigger('test_channel', 'my_event', array( 'encrypted' => 1 ));
		$this->assertInstanceOf('PusherTriggerResult', $triggerResult, 'Trigger with over encrypted connection');
	}

	public function testSendingOver10kBMessageReturns413() {
		$data = str_pad( '' , 11 * 1024, 'a' );
		echo( 'sending data of size: ' . mb_strlen( $data, '8bit' ) );

		try {
			$this->pusher->trigger('test_channel', 'my_event', $data );
		}
		catch(PusherHTTPException $e) {
			$this->assertEquals( 413, $e->status , '413 HTTP status response expected');
		}
	}

	/**
   * @expectedException PusherException
   */
	public function test_triggering_event_on_over_100_channels_throws_exception() {
		$channels = array();
		while( count( $channels ) <= 101 ) {
			$channels[] = ( 'channel-' . count( $channels ) );
		}
		$data = array( 'event_name' => 'event_data' );
		$response = $this->pusher->trigger( $channels, 'my_event', $data );
	}

	public function test_triggering_event_on_multiple_channels() {
		$data = array( 'event_name' => 'event_data' );
		$channels = array( 'test_channel_1', 'test_channel_2' );
		$triggerResult = $this->pusher->trigger( $channels, 'my_event', $data );

		$this->assertInstanceOf('PusherTriggerResult', $triggerResult);
	}
}

?>
