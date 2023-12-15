<?php

namespace unit;

use PHPUnit\Framework\TestCase;
use Pusher\Pusher;

class PusherConstructorTest extends TestCase
{
    public function testUseTLSOptionWillSetHostAndPort(): void
    {
        $options = ['useTLS' => true];
        $pusher = new Pusher('app_key', 'app_secret', 'app_id', $options);

        $settings = $pusher->getSettings();
        self::assertEquals('https', $settings['scheme'], 'https');
        self::assertEquals('api-mt1.pusher.com', $settings['host']);
        self::assertEquals('443', $settings['port']);
    }

    public function testUseTLSOptionWillBeOverwrittenByHostAndPortOptionsSetHostAndPort(): void
    {
        $options = [
            'useTLS' => true,
            'host' => 'test.com',
            'port' => '3000',
        ];
        $pusher = new Pusher('app_key', 'app_secret', 'app_id', $options);

        $settings = $pusher->getSettings();
        self::assertEquals('http', $settings['scheme']);
        self::assertEquals($options['host'], $settings['host']);
        self::assertEquals($options['port'], $settings['port']);
    }

    public function testSchemeIsStrippedAndIgnoredFromHostInOptions(): void
    {
        $options = [
            'host' => 'http://test.com',
        ];
        $pusher = new Pusher('app_key', 'app_secret', 'app_id', $options);

        $settings = $pusher->getSettings();
        self::assertEquals('https', $settings['scheme']);
        self::assertEquals('test.com', $settings['host']);
    }

    public function testClusterSetsANewHost(): void
    {
        $options = [
            'cluster' => 'eu',
        ];
        $pusher = new Pusher('app_key', 'app_secret', 'app_id', $options);

        $settings = $pusher->getSettings();
        self::assertEquals('api-eu.pusher.com', $settings['host']);
    }

    public function testClusterOptionIsOverriddenByHostIfItExists(): void
    {
        $options = [
            'cluster' => 'eu',
            'host' => 'api.staging.pusher.com',
        ];
        $pusher = new Pusher('app_key', 'app_secret', 'app_id', $options);

        $settings = $pusher->getSettings();
        self::assertEquals('api.staging.pusher.com', $settings['host']);
    }

    public function testSetTimeoutOption(): void
    {
        $options = [
            'timeout' => 10,
        ];
        $pusher = new Pusher('app_key', 'app_secret', 'app_id', $options);

        $settings = $pusher->getSettings();
        self::assertEquals(10, $settings['timeout']);
    }
}
