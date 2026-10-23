<?php

namespace unit;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Pusher\Pusher;
use stdClass;

class ChannelInfoUnitTest extends TestCase
{
    /**
     * @var Pusher
     */
    private $pusher;

    protected function setUp(): void
    {
        $this->pusher = new Pusher('thisisaauthkey', 'thisisasecret', 1);
    }

    public function testTrailingColonChannelThrowsException(): void
    {
        $this->expectException(\Pusher\PusherException::class);

        $this->pusher->get_channel_info('test_channel:');
    }

    public function testLeadingColonChannelThrowsException(): void
    {
        $this->expectException(\Pusher\PusherException::class);

        $this->pusher->get_channel_info(':test_channel');
    }

    public function testLeadingColonNLChannelThrowsException(): void
    {
        $this->expectException(\Pusher\PusherException::class);

        $this->pusher->get_channel_info(':\ntest_channel');
    }

    public function testTrailingColonNLChannelThrowsException(): void
    {
        $this->expectException(\Pusher\PusherException::class);

        $this->pusher->get_channel_info('test_channel\n:');
    }

    public function testGetChannelInfoUsesClientInterfaceRequest(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);

        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                'GET',
                'apps/appid/channels/test_channel',
                self::callback(function (array $options): bool {
                    return $options['http_errors'] === false
                        && $options['base_uri'] === 'https://api-test1.pusher.com:443'
                        && $options['headers']['Content-Type'] === 'application/json';
                })
            )
            ->willReturn(new Response(200, [], '{}'));

        $pusher = new Pusher('auth-key', 'secret', 'appid', ['cluster' => 'test1'], $httpClient);

        self::assertEquals(new stdClass(), $pusher->getChannelInfo('test_channel'));
    }

    public function getChannelsResponseProvider(): array
    {
        return [
            'channels object' => ['{"channels":{"presence-room":{},"private-orders":{}}}', ['presence-room', 'private-orders']],
            'empty channels object' => ['{"channels":{}}', []],
            'empty channels array' => ['{"channels":[]}', []],
        ];
    }

    /**
     * @dataProvider getChannelsResponseProvider
     */
    public function testGetChannelsReturnsChannelsKeyedByName(string $body, array $expectedChannels): void
    {
        $httpClient = $this->createMock(ClientInterface::class);

        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                'GET',
                'apps/appid/channels',
                self::callback(function (array $options): bool {
                    return $options['query']['filter_by_prefix'] === 'presence-';
                })
            )
            ->willReturn(new Response(200, [], $body));

        $pusher = new Pusher('auth-key', 'secret', 'appid', ['cluster' => 'test1'], $httpClient);

        $channels = $pusher->getChannels(['filter_by_prefix' => 'presence-'])->channels;

        self::assertIsArray($channels);
        self::assertSame($expectedChannels, array_keys($channels));
    }
}
