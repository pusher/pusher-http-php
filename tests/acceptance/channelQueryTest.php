<?php

class PusherChannelQueryTest extends PHPUnit\Framework\TestCase
{
    protected function setUp()
    {
        $this->pusher = new Pusher\Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, true, PUSHERAPP_HOST);
        $this->pusher->setLogger(new TestLogger());
    }

    public function testChannelInfo()
    {
        $response = $this->pusher->get_channel_info('channel-test');

        //print_r( $response );

        $this->assertObjectHasAttribute('occupied', $response, 'class has occupied attribute');
    }

    public function testChannelList()
    {
        $result = $this->pusher->get_channels();
        $channels = $result->channels;

        // print_r( $channels );

        foreach ($channels as $channel_name => $channel_info) {
            echo  "channel_name: $channel_name\n";
            echo  'channel_info: ';
            print_r($channel_info);
            echo  "\n\n";
        }

        $this->assertTrue(is_array($channels), 'channels is an array');
    }

    public function testFilterByPrefixNoChannels()
    {
        $options = array(
            'filter_by_prefix' => '__fish',
        );
        $result = $this->pusher->get_channels($options);

        // print_r( $result );

        $channels = $result->channels;

        // print_r( $channels );

        $this->assertTrue(is_array($channels), 'channels is an array');
        $this->assertEquals(0, count($channels), 'should be an empty array');
    }

    public function testFilterByPrefixOneChannel()
    {
        $options = array(
            'filter_by_prefix' => 'my-',
        );
        $result = $this->pusher->get_channels($options);

        // print_r( $result );

        $channels = $result->channels;

        // print_r( $channels );

        $this->assertEquals(1, count($channels), 'channels have a single test-channel present. For this test to pass you must have the "Getting Started" page open on the dashboard for the app you are testing against');
    }

    public function testUsersInfo()
    {
        $response = $this->pusher->get_users_info('presence-channel-test');

        // print_r( $response );

        $this->assertObjectHasAttribute('users', $response, 'class has users attribute');
    }

    public function testProvidingInfoParameterWithPrefixQueryFailsForPublicChannel()
    {
        $options = array(
            'filter_by_prefix' => 'test_',
            'info'             => 'user_count',
        );
        $result = $this->pusher->get_channels($options);

        $this->assertFalse($result, 'query should fail');
    }

    public function testChannelListUsingGenericGet()
    {
        $response = $this->pusher->get('/channels');

        $this->assertEquals($response['status'], 200);

        $result = $response['result'];

        $channels = $result['channels'];

        $this->assertEquals(1, count($channels), 'channels have a single my-channel present. For this test to pass you must have the "Getting Started" page open on the dashboard for the app you are testing against');

        $my_channel = $channels['my-channel'];

        $this->assertEquals(0, count($my_channel));
    }

    public function testChannelListUsingGenericGetAndPrefixParam()
    {
        $response = $this->pusher->get('/channels', array('filter_by_prefix' => 'my-'));

        $this->assertEquals($response['status'], 200);

        $result = $response['result'];

        $channels = $result['channels'];

        $this->assertEquals(1, count($channels), 'channels have a single my-channel present. For this test to pass you must have the "Getting Started" page open on the dashboard for the app you are testing against');

        $my_channel = $channels['my-channel'];

        $this->assertEquals(0, count($my_channel));
    }

    public function testSingleChannelInfoUsingGenericGet()
    {
        $response = $this->pusher->get('/channels/channel-test');

        $this->assertEquals($response['status'], 200);

        $result = $response['result'];

        $this->assertArrayHasKey('occupied', $result, 'class has occupied attribute');
    }
}
