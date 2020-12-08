<?php

    class PusherNotificationsUnitTest extends PHPUnit\Framework\TestCase
    {
        protected function setUp(): void
        {
            $this->pusher = new Pusher\Pusher('thisisaauthkey', 'thisisasecret', 1);
        }

        public function testInvalidEmptyInterests()
        {
            $this->expectException(\Pusher\PusherException::class);

            $this->pusher->notify(array(), array('foo' => 'bar'));
        }
    }
