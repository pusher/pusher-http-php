<?php

	require_once( dirname(__FILE__) . '/../test_includes.php' );
	
	class PusherAuthQueryString extends PHPUnit_Framework_TestCase
	{

		protected function setUp()
		{
		}

		public function testDebugCanBeSetViaLegacyParameter() {
			$pusher = new Pusher( 'app_key', 'app_secret', 'app_id', true );

			$settings = $pusher->getSettings();
			$this->assertEquals( $settings[ 'debug' ], true );
		}

		public function testHostCanBeSetViaLegacyParameter() {
			$host = 'http://test.com';
			$pusher = new Pusher( 'app_key', 'app_secret', 'app_id', false, $host );

			$settings = $pusher->getSettings();
			$this->assertEquals( $settings[ 'host' ], $host );
		}

		public function testPortCanBeSetViaLegacyParameter() {
			$host = 'http://test.com';
			$port = 90;
			$pusher = new Pusher( 'app_key', 'app_secret', 'app_id', false, $host, $port );

			$settings = $pusher->getSettings();
			$this->assertEquals( $settings[ 'port' ], $port );
		}

		public function testTimeoutCanBeSetViaLegacyParameter() {
			$host = 'http://test.com';
			$port = 90;
			$timeout = 90;
			$pusher = new Pusher( 'app_key', 'app_secret', 'app_id', false, $host, $port, $timeout );

			$settings = $pusher->getSettings();
			$this->assertEquals( $settings[ 'timeout' ], $timeout );
		}		

	}