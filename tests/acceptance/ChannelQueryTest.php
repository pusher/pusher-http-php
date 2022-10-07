<?php

namespace acceptance;

use PHPUnit\Framework\TestCase;
use Pusher\Pusher;

class ChannelQueryTest extends TestCase
{
    /**
     * @var Pusher
     */
    private $pusher;

    protected function setUp(): void
    {
        if (PUSHERAPP_AUTHKEY === '' || PUSHERAPP_SECRET === '' || PUSHERAPP_APPID === '') {
            self::markTestSkipped('Please set the
            PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET and
            PUSHERAPP_APPID keys.');
        } else {
            $this->pusher = new Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, ['cluster' => PUSHERAPP_CLUSTER]);
        }
    }

    public function testChannelInfo(): void
    {
        $result = $this->pusher->get_channel_info('channel-test');

        self::assertObjectHasAttribute('occupied', $result, 'class has occupied attribute');
    }

    public function testChannelList(): void
    {
        $result = $this->pusher->get_channels();
        $channels = $result->channels;

        self::assertIsArray($channels, 'channels is an array');
    }

    public function testFilterByPrefixNoChannels(): void
    {
        $options = [
            'filter_by_prefix' => '__fish',
        ];
        $result = $this->pusher->get_channels($options);

        $channels = $result->channels;

        self::assertIsArray($channels, 'channels is an array');
        self::assertCount(0, $channels, 'should be an empty array');
    }

    public function testFilterByPrefixOneChannel(): void
    {
        $options = [
            'filter_by_prefix' => CHANNEL_PREFIX,
        ];
        $result = $this->pusher->get_channels($options);

        $channels = $result->channels;

        $this->assertCount(1, $channels,
            'channels have a single test-channel present. For this test to pass you must have the "Getting Started" page open on the dashboard for the app you are testing against');
    }

    public function testUsersInfo(): void
    {
        $result = $this->pusher->get_users_info('presence-channel-test');

        $this->assertObjectHasAttribute('users', $result, 'class has users attribute');
    }

    public function testProvidingInfoParameterWithPrefixQueryFailsForPublicChannel(): void
    {
        $this->expectException(\Pusher\ApiErrorException::class);

        $options = [
            'filter_by_prefix' => 'test_',
            'info'             => 'user_count',
        ];
        $result = $this->pusher->get_channels($options);
    }

    public function testChannelListUsingGenericGet(): void
    {
        $result = $this->pusher->get('/channels', [], true);

        $channels = $result['channels'];

        self::assertGreaterThanOrEqual(1, $channels,
            'channels have a single my-channel present. For this test to pass you must have the "Getting Started" page open on the dashboard for the app you are testing against');

        $my_channel = $channels[TEST_CHANNEL];

        self::assertCount(0, $my_channel);
    }

    public function testChannelListUsingGenericGetAndPrefixParam(): void
    {
        $result = $this->pusher->get('/channels', ['filter_by_prefix' => CHANNEL_PREFIX], true);

        $channels = $result['channels'];

        self::assertCount(1, $channels,
            'channels have a single my-channel present. For this test to pass you must have the "Getting Started" page open on the dashboard for the app you are testing against');

        $my_channel = $channels[TEST_CHANNEL];

        self::assertCount(0, $my_channel);
    }

    public function testSingleChannelInfoUsingGenericGet(): void
    {
        $result = $this->pusher->get('/channels/channel-test');

        self::assertObjectHasAttribute('occupied', $result, 'class has occupied attribute');
    }
}
