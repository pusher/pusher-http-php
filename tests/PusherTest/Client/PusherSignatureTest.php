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

use Guzzle\Http\Message\Request as HttpRequest;
use PHPUnit_Framework_TestCase;
use Pusher\Client\Credentials;
use Pusher\Client\PusherSignature;

class PusherSignatureTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PusherSignature
     */
    protected $pusherSignature;

    /**
     * @var Credentials
     */
    protected $credentials;

    public function setUp()
    {
        $this->pusherSignature = new PusherSignature();
        $this->credentials     = new Credentials('3', '278d425bdf160c739803', '7ad3773142a6692b25b8');
    }

    /**
     * @covers PusherSignature::signRequest
     */
    public function testCanSignRequest()
    {
        $request = new HttpRequest('POST', '/apps/3/events');

        // We set variables in query to have always the same result
        $request->getQuery()->replace(array(
            'auth_key'       => $this->credentials->getKey(),
            'auth_timestamp' => '1353088179',
            'auth_version'   => '1.0',
            'body_md5'       => 'ec365a775a4cd0599faeb73354201b6f'
        ));

        $request->setResponseBody('{"name":"foo","channels":["project-3"],"data":"{\"some\":\"data\"}"}');

        $this->pusherSignature->signRequest($request, $this->credentials);

        $this->assertEquals('auth_key=278d425bdf160c739803&auth_timestamp=1353088179&auth_version=1.0&body_md5=ec365a775a4cd0599faeb73354201b6f&auth_signature=da454824c97ba181a32ccc17a72625ba02771f50b50e1e7430e47a1f3f457e6c', $request->getQuery('auth_signature'));
    }

    /**
     * @covers PusherSignature::signRequest
     */
    public function testAssertEmptyParametersAreStrippedWhenSignRequest()
    {
        $request = new HttpRequest('POST', '/apps/3/events');

        // We set variables in query to have always the same result
        $request->getQuery()->replace(array(
            'key-with-value'    => 'value',
            'key-without-value' => ''
        ));

        $this->pusherSignature->signRequest($request, $this->credentials);

        $queryParameters = $request->getQuery()->toArray();

        $this->assertArrayHasKey('key-with-value', $queryParameters);
        $this->assertArrayNotHasKey('key-without-value', $queryParameters);
    }

    /**
     * @covers PusherSignature::signPresenceChannel
     */
    public function testCanSignPresenceChannel()
    {
        $data   = array('user_id' => 10, 'user_info' => array('name' => 'Mr. Pusher'));
        $result = $this->pusherSignature->signPresenceChannel('presence-foobar', '1234.1234', $data, $this->credentials);

        $expectedResult = array(
            'auth'         => '278d425bdf160c739803:afaed3695da2ffd16931f457e338e6c9f2921fa133ce7dac49f529792be6304c',
            'channel_data' => '{"user_id":10,"user_info":{"name":"Mr. Pusher"}}'
        );

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @covers PusherSignature::signPrivateChannel
     */
    public function testCanSignPrivateChannel()
    {
        $result = $this->pusherSignature->signPrivateChannel('private-foobar', '1234.1234', $this->credentials);

        $expectedResult = array(
            'auth' => '278d425bdf160c739803:58df8b0c36d6982b82c3ecf6b4662e34fe8c25bba48f5369f135bf843651c3a4'
        );

        $this->assertEquals($expectedResult, $result);
    }
}
