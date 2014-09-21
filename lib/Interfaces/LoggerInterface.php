<?php namespace Pusher\Interfaces;

/**
 * @package     Pusher
 * @copyright   2011,   Squeeks
 * @licence     http://www.opensource.org/licenses/mit-license.php  MIT
 */

interface LoggerInterface
{
    /**
     * @param string $data
     * @return void
     */
    public function log($data);
} 