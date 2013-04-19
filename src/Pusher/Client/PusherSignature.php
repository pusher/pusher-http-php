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
