<?php

    class PusherNotificationsUnitTest extends PHPUnit_Framework_TestCase
    {
        protected function setUp()
        {
            $this->pusher = new Pusher('thisisaauthkey', 'thisisasecret', 1);
        }

        /**
         * @expectedException PusherException
         */
        public function testInvalidInterestLength()
        {
            $this->pusher->notify(array('a', 'b'), array('foo' => 'bar'));
        }
    }
