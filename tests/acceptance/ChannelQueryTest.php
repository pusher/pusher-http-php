<?php

class ChannelQueryTest extends PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        $this->pusher = new Pusher\Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, ['host' => PUSHERAPP_HOST]);
    }

    public function testChannelInfo()
    {
        $result = $this->pusher->get_channel_info('channel-test');

        $this->assertObjectHasAttribute('occupied', $result, 'class has occupied attribute');
    }

    public function testChannelList()
    {
        $result = $this->pusher->get_channels();
        $channels = $result->channels;

        $this->assertTrue(is_array($channels), 'channels is an array');
    }

    public function testFilterByPrefixNoChannels()
    {
        $options = array(
            'filter_by_prefix' => '__fish',
        );
        $result = $this->pusher->get_channels($options);

        $channels = $result->channels;

        $this->assertTrue(is_array($channels), 'channels is an array');
        $this->assertEquals(0, count($channels), 'should be an empty array');
    }

    public function testFilterByPrefixOneChannel()
    {
        $options = array(
            'filter_by_prefix' => 'my-',
        );
        $result = $this->pusher->get_channels($options);

        $channels = $result->channels;

        $this->assertEquals(1, count($channels), 'channels have a single test-channel present. For this test to pass you must have the "Getting Started" page open on the dashboard for the app you are testing against');
    }

    public function testUsersInfo()
    {
        $result = $this->pusher->get_users_info('presence-channel-test');

        $this->assertObjectHasAttribute('users', $result, 'class has users attribute');
    }

    public function testProvidingInfoParameterWithPrefixQueryFailsForPublicChannel()
    {
        $this->expectException(\Pusher\ApiErrorException::class);

        $options = array(
            'filter_by_prefix' => 'test_',
            'info'             => 'user_count',
        );
        $result = $this->pusher->get_channels($options);
    }

    public function testChannelListUsingGenericGet()
    {
        $result = $this->pusher->get('/channels', array(), true);

        $channels = $result['channels'];

        $this->assertEquals(1, count($channels), 'channels have a single my-channel present. For this test to pass you must have the "Getting Started" page open on the dashboard for the app you are testing against');

        $my_channel = $channels['my-channel'];

        $this->assertEquals(0, count($my_channel));
    }

    public function testChannelListUsingGenericGetAndPrefixParam()
    {
        $result = $this->pusher->get('/channels', array('filter_by_prefix' => 'my-'), true);

        $channels = $result['channels'];

        $this->assertEquals(1, count($channels), 'channels have a single my-channel present. For this test to pass you must have the "Getting Started" page open on the dashboard for the app you are testing against');

        $my_channel = $channels['my-channel'];

        $this->assertEquals(0, count($my_channel));
    }

    public function testSingleChannelInfoUsingGenericGet()
    {
        $result = $this->pusher->get('/channels/channel-test');

        $this->assertObjectHasAttribute('occupied', $result, 'class has occupied attribute');
    }
}
