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

namespace Pusher\Client\Listener;

use Guzzle\Common\Event;
use Pusher\Client\Credentials;
use Pusher\Client\PusherSignature;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SignatureListener implements EventSubscriberInterface
{
    /**
     * @var Credentials
     */
    protected $credentials;

    /**
     * @var PusherSignature
     */
    protected $signature;

    /**
     * @param Credentials $credentials
     * @param PusherSignature $signature
     */
    public function __construct(Credentials $credentials, PusherSignature $signature)
    {
        $this->credentials = $credentials;
        $this->signature   = $signature;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            'request.before_send' => array('onRequestBeforeSend', -255)
        );
    }

    /**
     * Signs requests before they are sent
     *
     * @param  Event $event
     * @return void
     */
    public function onRequestBeforeSend(Event $event)
    {
        $this->signature->signRequest($event['request'], $this->credentials);
    }
}
