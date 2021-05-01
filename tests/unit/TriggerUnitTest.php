<?php

namespace unit;

use PHPUnit\Framework\TestCase;
use Pusher\Pusher;
use Pusher\PusherException;

class TriggerUnitTest extends TestCase
{
    /**
     * @var array
     */
    private $localData;
    /**
     * @var string
     */
    private $eventName;
    /**
     * @var Pusher
     */
    private $pusher;

    protected function setUp(): void
    {
        $this->pusher = new Pusher('thisisaauthkey', 'thisisasecret', 1);
        $this->eventName = 'test_event';
        $this->localData = [];
    }

    public function testTrailingColonChannelThrowsException(): void
    {
        $this->expectException(PusherException::class);

        $this->pusher->trigger('test_channel:', $this->eventName, $this->localData);
    }

    public function testLeadingColonChannelThrowsException(): void
    {
        $this->expectException(PusherException::class);

        $this->pusher->trigger(':test_channel', $this->eventName, $this->localData);
    }

    public function testLeadingColonNLChannelThrowsException(): void
    {
        $this->expectException(PusherException::class);

        $this->pusher->trigger(':\ntest_channel', $this->eventName, $this->localData);
    }

    public function testTrailingColonNLChannelThrowsException(): void
    {
        $this->expectException(PusherException::class);

        $this->pusher->trigger('test_channel\n:', $this->eventName, $this->localData);
    }

    public function testChannelArrayThrowsException(): void
    {
        $this->expectException(PusherException::class);

        $this->pusher->trigger(['this_one_is_okay', 'test_channel\n:'], $this->eventName, $this->localData);
    }

    public function testTrailingColonSocketIDThrowsException(): void
    {
        $this->expectException(PusherException::class);

        $this->pusher->trigger('test_channel:', $this->eventName, $this->localData, ['socket_id' => '1.1:']);
    }

    public function testLeadingColonSocketIDThrowsException(): void
    {
        $this->expectException(PusherException::class);

        $this->pusher->trigger('test_channel:', $this->eventName, $this->localData, ['socket_id' => ':1.1']);
    }

    public function testLeadingColonNLSocketIDThrowsException(): void
    {
        $this->expectException(PusherException::class);

        $this->pusher->trigger('test_channel:', $this->eventName, $this->localData, ['socket_id' => ':\n1.1']);
    }

    public function testTrailingColonNLSocketIDThrowsException(): void
    {
        $this->expectException(PusherException::class);

        $this->pusher->trigger('test_channel:', $this->eventName, $this->localData, ['socket_id' => '1.1\n:']);
    }

    public function testFalseSocketIDThrowsException(): void
    {
        $this->expectException(PusherException::class);

        $this->pusher->trigger('test_channel', $this->eventName, $this->localData, ['socket_id' => false]);
    }

    public function testEmptyStrSocketIDThrowsException(): void
    {
        $this->expectException(PusherException::class);

        $this->pusher->trigger('test_channel', $this->eventName, $this->localData, ['socket_id' => '']);
    }
}
