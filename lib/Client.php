<?php namespace Pusher;

/**
 * @package     Pusher
 * @copyright   2011,   Squeeks
 * @licence     http://www.opensource.org/licenses/mit-license.php  MIT
 */


class Client
{
    /**
     * Name of user agent to expose to API endpoints
     */
    const USER_AGENT = 'Pusher/PHP';

    /**
     * @var resource
     */
    private $curl;

    /**
     * @var string
     */
    private $server;

    /**
     * @var integer
     */
    private $port;

    /**
     * @var string
     */
    private $authKey;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var int
     */
    private $timeout;

    /**
     * @param     $server
     * @param     $port
     * @param     $authKey
     * @param     $secret
     * @param int $timeout
     */
    public function __construct($server, $port, $authKey, $secret, $timeout = 30)
    {
        $this->server = $server;
        $this->port = $port;
        $this->authKey = $authKey;
        $this->secret = $secret;
        $this->timeout = $timeout;

        $this->curl = curl_init();

        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($this->curl, CURLOPT_USERAGENT, self::USER_AGENT . ' - v' . Pusher::VERSION);
    }