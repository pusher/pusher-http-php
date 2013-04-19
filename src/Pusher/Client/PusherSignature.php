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

namespace Pusher\Client;

use Guzzle\Http\Message\EntityEnclosingRequestInterface;
use Guzzle\Http\Message\RequestInterface;

/**
 * @licence MIT
 */
class PusherSignature
{
    /**
     * Sign the Pusher request
     *
     * @link  http://pusher.com/docs/rest_api#authentication
     * @param RequestInterface $request
     * @param Credentials $credentials
     */
    public function signRequest(RequestInterface $request, Credentials $credentials)
    {
        $queryParameters = array(
            'auth_key'       => $credentials->getAccessKey(),
            'auth_timestamp' => time(),
            'auth_version'   => '1.0'
        );

        if ($request instanceof EntityEnclosingRequestInterface) {
            $body                        = $request->getBody();
            $queryParameters['body_md5'] = $body->getContentLength() ? $body->getContentMd5() : '';
        }

        // We need to traverse each Query parameter to make sure the key is lowercased
        foreach ($request->getQuery() as $key => $value) {
            $queryParameters[strtolower($key)] = $value;
        }

        ksort($queryParameters);

        $method      = strtoupper($request->getMethod());
        $requestPath = $request->getPath();
        $query       = urldecode(http_build_query(array_filter($queryParameters)));

        $signature = $this->signString(implode(PHP_EOL, array($method, $requestPath, $query)), $credentials);

        $queryParameters['auth_signature'] = $signature;

        $request->getQuery()->replace($queryParameters);
    }

    /**
     * Authenticate a user (identified by its socket identifier) to a presence channel. This method returns
     * an array whose key is "auth" and value is the signature. It's up to the user to return this correctly
     * into a JSON string (typically in a controller)
     *
     * @link http://pusher.com/docs/auth_signatures#presence
     * @param  string $channel
     * @param  string $socketId
     * @param  array $data
     * @param  Credentials $credentials
     * @return array
     */
    public function signPresenceChannel($channel, $socketId, array $data, Credentials $credentials)
    {
        $data = json_encode($data);

        $stringToSign = $socketId . ':' . $channel . ':' . $data;
        $signature    = $this->signString($stringToSign, $credentials);

        return array(
            'auth'         => $credentials->getAccessKey() . ':' . $signature,
            'channel_data' => $data
        );
    }

    /**
     * Authenticate a user (identified by its socket identifier) to a private channel. This method returns
     * an array whose key is "auth" and value is the signature. It's up to the user to return this correctly
     * into a JSON string (typically in a controller)
     *
     * @link http://pusher.com/docs/auth_signatures#worked-example
     * @param  string $channel
     * @param  string $socketId
     * @param  Credentials $credentials
     * @return array
     */
    public function signPrivateChannel($channel, $socketId, Credentials $credentials)
    {
        $stringToSign = $socketId . ':' . $channel;
        $signature    = $this->signString($stringToSign, $credentials);

        return array(
            'auth' => $credentials->getAccessKey() . ':' . $signature
        );
    }

    /**
     * Sign the authentication string
     *
     * @param  string $string
     * @param  Credentials $credentials
     * @return string
     */
    public function signString($string, Credentials $credentials)
    {
        return hash_hmac('sha256', $string, $credentials->getSecretKey());
    }
}
