<?php namespace Pusher;

/**
 * @package     Pusher
 * @copyright   2011,   Squeeks
 * @licence     http://www.opensource.org/licenses/mit-license.php  MIT
 */

class PusherInstance
{

    /**
     * @var Pusher
     */
    private static $instance = null;

    /**
     * @var string
     */
    private static $appId = '';

    /**
     * @var string
     */
    private static $secret	= '';

    /**
     * @var string
     */
    private static $apiKey = '';

    /**
     * Disable object instantiation
     */
    private function __construct() {}

    /**
     * Disable cloning of object
     */
    private function __clone() {}

    /**
     * @return Pusher
     */
    public static function getPusher()
    {
        if(self::$instance !== null) return self::$instance;

        self::$instance = new Pusher(
            self::$apiKey,
            self::$secret,
            self::$appId
        );

        return self::$instance;
    }
}
