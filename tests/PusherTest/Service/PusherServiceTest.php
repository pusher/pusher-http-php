<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace PusherTest\Service;

use Guzzle\Common\Collection;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Message\Response;
use Guzzle\Service\ClientInterface;
use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Pusher\Service\PusherService;
use ReflectionMethod;
use Symfony\Component\EventDispatcher\EventDispatcher;

class PusherServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PusherService
     */
    protected $service;

    /**
     * @var ClientInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $client;

    public function setUp()
    {
        $methodsToMock = array('trigger', 'getChannelsInfo', 'getChannelInfo', 'getPresenceUsers',
                               'addSubscriber', 'removeSubscriber');

        $this->client  = $this->getMock('Pusher\Client\PusherClient', $methodsToMock, array(), '', false);
        $this->service = new PusherService($this->client);

        $this->client->expects($this->any())
                     ->method('getEventDispatcher')
                     ->will($this->returnValue(new EventDispatcher()));
    }

    /**
     * @covers PusherService::trigger
     */
    public function testAsyncPluginCanBeAdded()
    {
        $method = new ReflectionMethod('Pusher\Service\PusherService', 'getAsyncPlugin');
        $method->setAccessible(true);

        $this->client->expects($this->once())
                     ->method('addSubscriber')
                     ->with($method->invoke($this->service));

        $this->service->trigger('my-event', 'my-channel', array(), '', true);
    }

    /**
     * @covers PusherService::trigger
     */
    public function testTriggerDoesNotAddAsyncPluginByDefault()
    {
        $method = new ReflectionMethod('Pusher\Service\PusherService', 'getAsyncPlugin');
        $method->setAccessible(true);

        $this->client->expects($this->never())
                     ->method('addSubscriber');

        $this->service->trigger('my-event', 'my-channel', array(), '');
    }

    /**
     * @covers PusherService::triggerAsync
     */
    public function testTriggerAsyncAddAsyncPlugin()
    {
        $method = new ReflectionMethod('Pusher\Service\PusherService', 'getAsyncPlugin');
        $method->setAccessible(true);

        $this->client->expects($this->once())
                     ->method('addSubscriber')
                     ->with($method->invoke($this->service));

        $this->service->triggerAsync('my-event', 'my-channel', array(), '');
    }

    /**
     * @covers PusherService::trigger
     */
    public function testTriggerUseChannelIfString()
    {
        $expectedParameters = array(
            'event'     => 'my-event',
            'channel'   => 'my-channel',
            'data'      => array(),
            'socket_id' => ''
        );

        $this->client->expects($this->once())
                     ->method('trigger')
                     ->with($expectedParameters);

        $this->service->trigger('my-event', 'my-channel', array());
    }

    /**
     * @covers PusherService::trigger
     */
    public function testTriggerUseChannelsIfArray()
    {
        $expectedParameters = array(
            'event'     => 'my-event',
            'channels'  => array('my-channel-1', 'my-channel-2'),
            'data'      => array(),
            'socket_id' => ''
        );

        $this->client->expects($this->once())
                     ->method('trigger')
                     ->with($expectedParameters);

        $this->service->trigger('my-event', array('my-channel-1', 'my-channel-2'), array());
    }

    /**
     * @covers PusherService::getChannelsInfo
     */
    public function testFormatParametersWhenGetChannelsInfo()
    {
        $expectedParameters = array(
            'prefix' => 'presence-',
            'info'   => array('user_count')
        );

        $this->client->expects($this->once())
                     ->method('getChannelsInfo')
                     ->with($expectedParameters)
                     ->will($this->returnValue(new Collection()));

        $this->service->getChannelsInfo('presence-', array('user_count'));
    }

    /**
     * @covers PusherService::getChannelInfo
     */
    public function testFormatParametersWhenGetChannelInfo()
    {
        $expectedParameters = array(
            'channel' => 'private-foobar',
            'info'    => array('user_count')
        );

        $this->client->expects($this->once())
                     ->method('getChannelInfo')
                     ->with($expectedParameters)
                     ->will($this->returnValue(new Collection()));

        $this->service->getChannelInfo('private-foobar', array('user_count'));
    }

    /**
     * @covers PusherService::getPresenceUsers
     */
    public function testFormatParametersWhenGetPresenceUsers()
    {
        $expectedParameters = array(
            'channel' => 'presence-foobar'
        );

        $this->client->expects($this->once())
                     ->method('getPresenceUsers')
                     ->with($expectedParameters)
                     ->will($this->returnValue(new Collection()));

        $this->service->getPresenceUsers('presence-foobar');
    }

    public function exceptionDataProvider()
    {
        return array(
            array(200, null),
            array(400, 'Pusher\Service\Exception\RuntimeException'),
            array(401, 'Pusher\Service\Exception\UnauthorizedException'),
            array(403, 'Pusher\Service\Exception\ForbiddenException'),
            array(404, 'Pusher\Service\Exception\UnknownResourceException'),
            array(500, 'Pusher\Service\Exception\RuntimeException'),
        );
    }

    /**
     * @covers PusherService::handleException
     * @dataProvider exceptionDataProvider
     */
    public function testExceptionsAreThrownOnErrors($statusCode, $expectedException)
    {
        $method = new ReflectionMethod('Pusher\Service\PusherService', 'handleException');
        $method->setAccessible(true);

        $exception = new BadResponseException();
        $exception->setResponse(new Response($statusCode));

        $this->setExpectedException($expectedException);

        $method->invoke($this->service, $exception);
    }
}
