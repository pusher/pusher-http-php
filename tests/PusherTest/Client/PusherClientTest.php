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

namespace PusherTest\Client;

use PHPUnit_Framework_TestCase;
use Pusher\Client\Credentials;
use Pusher\Client\PusherClient;

class PusherClientTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PusherClient
     */
    protected $client;

    /**
     * @var Credentials
     */
    protected $credentials;

    public function setUp()
    {
        $this->credentials = new Credentials('3', '278d425bdf160c739803', '7ad3773142a6692b25b8');
        $this->client      = new PusherClient($this->credentials);
    }

    /**
     * @covers PusherClient::__construct
     */
    public function testAssertApplicationIdIsAlwaysSent()
    {
        $config = $this->client->getConfig('command.params');
        $this->assertEquals($config['app_id'], $this->credentials->getAppId());
    }

    /**
     * @covers PusherClient::getApiVersion
     */
    public function testCanRetrieveApiVersion()
    {
        $this->assertEquals('1.0', $this->client->getApiVersion());
    }

    /**
     * @covers PusherClient
     */
    public function testUserAgentIsIncluded()
    {
        // Make sure the user agent contains "pusher-php"
        $command = $this->client->getCommand('GetChannelsInfo');
        $request = $command->prepare();
        $this->client->dispatch('command.before_send', array('command' => $command));
        $this->assertRegExp('/^pusher-php/', $request->getHeader('User-Agent', true));
    }
}
