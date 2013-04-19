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

use Guzzle\Service\Client;
use Guzzle\Service\Description\ServiceDescription;
use Guzzle\Service\Resource\Model;
use Pusher\Client\Listener\SignatureListener;
use Pusher\Version;

/**
 * Client to interact with Pusher REST API
 *
 * @method Model getChannelInfo(array $args = array()) {@command Pusher GetChannelInfo}
 * @method Model getChannelsInfo(array $args = array()) {@command Pusher GetChannelsInfo}
 * @method Model getPresenceUsers(array $args = array()) {@command Pusher GetPresenceUsers}
 * @method Model trigger(array $args = array()) {@command Pusher Trigger}
 *
 * @licence MIT
 */
class PusherClient extends Client
{
    /**
     * Pusher API version
     */
    const LATEST_API_VERSION = '1.0';

    /**
     * @var Credentials
     */
    protected $credentials;

    /**
     * @var PusherSignature
     */
    protected $signature;

    /**
     * Constructor
     *
     * @param Credentials $credentials
     */
    public function __construct(Credentials $credentials)
    {
        // Make sure we always have the app_id parameter as default
        parent::__construct('', array(
            'command.params' => array(
                'app_id' => $credentials->getAppId()
            )
        ));

        $this->credentials = $credentials;

        $this->setDescription(ServiceDescription::factory(sprintf(
            __DIR__ . '/ServiceDescription/Pusher-%s.php',
            self::LATEST_API_VERSION
        )));

        // Prefix the User-Agent by SDK version
        $this->setUserAgent('pusher-php/' . Version::VERSION, true);

        $this->signature = new PusherSignature();

        // Add a listener to sign each requests
        $this->addSubscriber(new SignatureListener($credentials, $this->signature));
    }

    /**
     * {@inheritdoc}
     */
    public function __call($method, $args = array())
    {
        return parent::__call(ucfirst($method), $args);
    }

    /**
     * Get the Pusher Credentials
     *
     * @return Credentials
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * Get the Pusher Signature
     *
     * @return PusherSignature
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * Get current Pusher API version
     *
     * @return string
     */
    public function getApiVersion()
    {
        return $this->serviceDescription->getApiVersion();
    }
}
