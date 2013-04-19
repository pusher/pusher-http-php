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

namespace Pusher\Service;

use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Plugin\Async\AsyncPlugin;
use Pusher\Client\PusherClient;

/**
 * Simple wrapper around PusherClient to simplify its use
 *
 * @licence MIT
 */
class PusherService
{
    /**
     * @var PusherClient
     */
    protected $client;

    /**
     * Constructor
     *
     * @param PusherClient $client
     */
    public function __construct(PusherClient $client)
    {
        $this->client = $client;
    }

    /**
     * ----------------------------------------------------------------------------------------------------
     * EVENTS
     * ----------------------------------------------------------------------------------------------------
     */

    /**
     * Trigger a new event
     *
     * @link http://pusher.com/docs/rest_api#method-post-event
     * @param  string        $event    Event name
     * @param  array|string  $channels Single or list of channels
     * @param  array         $data     Event data (limited to 10 Kb)
     * @param  string        $socketId Exclude a specific socket id from the event
     * @param  bool          $async    If true, the request is performed asynchronously
     * @return void
     */
    public function trigger($event, $channels, array $data = array(), $socketId = '', $async = false)
    {
        $parameters = array(
            'event'     => $event,
            'data'      => $data,
            'socket_id' => $socketId
        );

        // @TODO: we may also wrap a single channel name in an array, so we can remove the conditional and
        //        always use 'channels' key, but it may be useful to Pusher team for statistics purposes

        if (is_string($channels)) {
            $parameters['channel'] = $channels;
        } elseif (is_array($channels)) {
            $parameters['channels'] = $channels;
        }

        if ($async) {
            $this->client->addSubscriber(new AsyncPlugin());
        }

        try {
            $this->client->trigger($parameters);
        } catch (BadResponseException $exception) {
            $this->handleException($exception);
        }
    }

    /**
     * ----------------------------------------------------------------------------------------------------
     * CHANNELS
     * ----------------------------------------------------------------------------------------------------
     */

    /**
     * Get information about multiple channels, optionally filtered by a prefix
     *
     * @link   http://pusher.com/docs/rest_api#method-get-channels
     * @param  string $prefix A string used to filter channels by prefix
     * @param  array  $info   An array that contains valid info to retrieve
     * @return array
     */
    public function getChannelsInfo($prefix = '', array $info = array())
    {
        try {
            return $this->client->getChannelsInfo(compact('prefix', 'info'))->toArray();
        } catch (BadResponseException $exception) {
            $this->handleException($exception);
        }
    }

    /**
     * Get information about a single channel identified by its name
     *
     * @link   http://pusher.com/docs/rest_api#method-get-channel
     * @param  string $channel A channel name
     * @param  array  $info    An array that contains valid info to retrieve
     * @return array
     */
    public function getChannelInfo($channel, array $info = array())
    {
        try {
            return $this->client->getChannelInfo(compact('channel', 'info'));
        } catch (BadResponseException $exception) {
            $this->handleException($exception);
        }
    }

    /**
     * ----------------------------------------------------------------------------------------------------
     * USERS
     * ----------------------------------------------------------------------------------------------------
     */

    /**
     * Get a list of user identifiers that are currently subscribed to a channel identified by its name. Note
     * that only presence channels (whose name begins by presence-) are allowed here
     *
     * @link   http://pusher.com/docs/rest_api#method-get-users
     * @param  string $channel A presence channel name
     * @return array
     */
    public function getPresenceUsers($channel)
    {
        try {
            return $this->client->getPresenceUsers(compact('channel'))->toArray();
        } catch (BadResponseException $exception) {
            $this->handleException($exception);
        }
    }

    /**
     * ----------------------------------------------------------------------------------------------------
     * AUTHENTICATION FOR PRESENCE AND PRIVATE CHANNELS
     * ----------------------------------------------------------------------------------------------------
     */

    /**
     * Authenticate a user (identified by its socket identifier) to a presence channel. This method returns
     * an array whose key is "auth" and value is the signature. It's up to the user to return this correctly
     * into a JSON string (typically in a controller)
     *
     * @link http://pusher.com/docs/auth_signatures#presence
     * @param  string $channel
     * @param  string $socketId
     * @param  array $data
     * @return array
     */
    public function authenticatePresence($channel, $socketId, array $data)
    {
        $credentials = $this->client->getCredentials();
        $signature   = $this->client->getSignature();

        return $signature->signPresenceChannel($channel, $socketId, $data, $credentials);
    }

    /**
     * Authenticate a user (identified by its socket identifier) to a private channel. This method returns
     * an array whose key is "auth" and value is the signature. It's up to the user to return this correctly
     * into a JSON string (typically in a controller)
     *
     * @link http://pusher.com/docs/auth_signatures#worked-example
     * @param  string $channel
     * @param  string $socketId
     * @return array
     */
    public function authenticatePrivate($channel, $socketId)
    {
        $credentials = $this->client->getCredentials();
        $signature   = $this->client->getSignature();

        return $signature->signPrivateChannel($channel, $socketId, $credentials);
    }

    /**
     * Throw specific Pusher exceptions according to the status code
     *
     * @param  BadResponseException $exception
     * @throws Exception\UnauthorizedException
     * @throws Exception\ForbiddenException
     * @throws Exception\RuntimeException
     * @throws Exception\UnknownResourceException
     * @return void
     */
    protected function handleException(BadResponseException $exception)
    {
        $response = $exception->getResponse();

        if ($response->isSuccessful()) {
            return;
        }

        // Reason is injected into the body content, however we do not really want to output the whole
        // body content to the user, but rather only the real reason, which is typically the last line
        // of the body content
        $body    = array_filter(explode(PHP_EOL, $response->getMessage()));
        $message = end($body);

        switch($response->getStatusCode()) {
            case 400:
                throw new Exception\RuntimeException(sprintf(
                    'An error occurred while trying to handle your request. Reason: %s',
                    $message
                ), 400);
            case 401:
                throw new Exception\UnauthorizedException(sprintf(
                    'You are not authorized to perform this action. Reason: %s',
                    $message
                ), 401);
            case 403:
                throw new Exception\ForbiddenException(sprintf(
                    'You are not allowed to perform this action, your application may be disabled or you may have reached your message quota. Reason: %s',
                    $message
                ), 403);
            case 404:
                throw new Exception\UnknownResourceException(sprintf(
                    'Resource cannot be found, are you sure it exists? Reason: %s',
                    $message
                ), 404);
            default:
                throw new Exception\RuntimeException(sprintf(
                    'An unknown error occurred on Pusher side. Reason: %s',
                    $message
                ), $response->getStatusCode());
        }
    }
}
