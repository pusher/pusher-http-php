<?php

namespace Pusher\Tests;

use Pusher\Config;
use Pusher\WebHook;

/**
 * @covers pusher\WebHook
 */
class WebHookTest extends \PHPUnit_Framework_TestCase
{

    public function testSimple()
    {
        $config = new Config(array(
            'keys' => array('4d2c3d146b1b662605b7' => '2063025205ec9774f5e1')
        ));
        $body = '{"time_ms":1404402287570,"events":[{"channel":"presence-messages","user_id":"49294081","name":"member_removed"}]}';
        $api_key = '4d2c3d146b1b662605b7';
        $signature = '3c9d14cf02cff582b5b7bf050a42b7e7cf700ab8be9372e57decee7ff19dd849';

        $wh = new WebHook($config, $api_key, $signature, __dir__ . '/body1.txt');

        $this->assertTrue($wh->valid());
        $this->assertEquals($body, $wh->body);
        $this->assertEquals(1, count($wh->events()));
        $this->assertEquals(1404402287, $wh->timestamp());
    }
}
