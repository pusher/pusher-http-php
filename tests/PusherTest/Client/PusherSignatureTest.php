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
    public function testCanSignRequest()
    {
        $credentials = new Credentials('3', '278d425bdf160c739803', '7ad3773142a6692b25b8');
        $request     = new HttpRequest('POST', '/apps/3/events');

        $pusherSignature = new PusherSignature();

        // We set variables in query to have always the same result
        $request->getQuery()->replace(array(
            'auth_key'       => $credentials->getAccessKey(),
            'auth_timestamp' => '1353088179',
            'auth_version'   => '1.0',
            'body_md5'       => 'ec365a775a4cd0599faeb73354201b6f'
        ));

        $request->setResponseBody('{"name":"foo","channels":["project-3"],"data":"{\"some\":\"data\"}"}');

        $pusherSignature->signRequest($request, $credentials);

        $this->assertEquals('auth_key=278d425bdf160c739803&auth_timestamp=1353088179&auth_version=1.0&body_md5=ec365a775a4cd0599faeb73354201b6f&auth_signature=da454824c97ba181a32ccc17a72625ba02771f50b50e1e7430e47a1f3f457e6c', $request->getQuery('auth_signature'));
    }
}
