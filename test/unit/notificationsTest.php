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

        /**
         * @expectedException PusherException
         */
        public function testMissingGcmApnsKeys()
        {
            $this->pusher->notify(array('test'), array('foo' => 'bar'));
        }

        /**
         * @expectedException PusherException
         */
        public function testInvalidTimeToLive()
        {
            $payload = array(
                'gcm' => array(
                    'time_to_live' => 21273871,
                    'notification' => array(
                        'title' => 'title',
                        'icon' => 'icon',
                    ),
                ),
            );
            $this->pusher->notify(array('test'), $payload);
        }

        /**
         * @expectedException PusherException
         */
        public function testMissingNotificationTitle()
        {
            $payload = array(
                'gcm' => array(
                    'notification' => array(
                        'icon' => 'icon',
                    ),
                ),
            );
            $this->pusher->notify(array('test'), $payload);
        }

        /**
         * @expectedException PusherException
         */
        public function testMissingNotificationIcon()
        {
            $payload = array(
                'gcm' => array(
                    'notification' => array(
                        'title' => 'title',
                    ),
                ),
            );
            $this->pusher->notify(array('test'), $payload);
        }

        /**
         * @expectedException PusherException
         */
        public function testInvalidWebhookUrl()
        {
            $payload = array(
                'gcm' => array(
                    'foo' => 'bar',
                ),
                'webhook_url' => 'aksdasd',
            );
            $this->pusher->notify(array('test'), $payload);
        }

        /**
         * @expectedException PusherException
         */
        public function testInvalidWebhookLevel()
        {
            $payload = array(
                'gcm' => array(
                    'foo' => 'bar',
                ),
                'webhook_url' => 'https://test.com/wh',
                'webhook_level' => 'WARN',
            );
            $this->pusher->notify(array('test'), $payload);
        }

        /**
         * @expectedException PusherException
         */
        public function testWebhookLevelWithoutUrl()
        {
            $payload = array(
                'gcm' => array('foo' => 'bar'),
                'webhook_level' => 'INFO',
            );
            $this->pusher->notify(array('test'), $payload);
        }
    }
