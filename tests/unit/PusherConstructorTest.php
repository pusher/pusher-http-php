<?php

class PusherConstructorTest extends PHPUnit\Framework\TestCase
{
    public function testUseTLSOptionWillSetHostAndPort()
    {
        $options = array('useTLS' => true);
        $pusher = new Pusher\Pusher('app_key', 'app_secret', 'app_id', $options);

        $settings = $pusher->getSettings();
        $this->assertEquals('https', $settings['scheme'], 'https');
        $this->assertEquals('api-mt1.pusher.com', $settings['host']);
        $this->assertEquals('443', $settings['port']);
    }

    public function testUseTLSOptionWillBeOverwrittenByHostAndPortOptionsSetHostAndPort()
    {
        $options = array(
            'useTLS' => true,
            'host'   => 'test.com',
            'port'   => '3000',
        );
        $pusher = new Pusher\Pusher('app_key', 'app_secret', 'app_id', $options);

        $settings = $pusher->getSettings();
        $this->assertEquals('http', $settings['scheme']);
        $this->assertEquals($options['host'], $settings['host']);
        $this->assertEquals($options['port'], $settings['port']);
    }

    public function testSchemeIsStrippedAndIgnoredFromHostInOptions()
    {
        $options = array(
            'host' => 'http://test.com',
        );
        $pusher = new Pusher\Pusher('app_key', 'app_secret', 'app_id', $options);

        $settings = $pusher->getSettings();
        $this->assertEquals('https', $settings['scheme']);
        $this->assertEquals('test.com', $settings['host']);
    }

    public function testClusterSetsANewHost()
    {
        $options = array(
            'cluster' => 'eu',
        );
        $pusher = new Pusher\Pusher('app_key', 'app_secret', 'app_id', $options);

        $settings = $pusher->getSettings();
        $this->assertEquals('api-eu.pusher.com', $settings['host']);
    }

    public function testClusterOptionIsOverriddenByHostIfItExists()
    {
        $options = array(
            'cluster' => 'eu',
            'host'    => 'api.staging.pusher.com',
        );
        $pusher = new Pusher\Pusher('app_key', 'app_secret', 'app_id', $options);

        $settings = $pusher->getSettings();
        $this->assertEquals('api.staging.pusher.com', $settings['host']);
    }
}
