<?php

class PusherAutoloader
{
    private static $classMap = array(
        'Pusher' => 'Pusher.php',
        'PusherInstance' => 'Pusher.php',
        'PusherException' => 'PusherException.php',
        'PusherHTTPException' => 'PusherHTTPException.php',
        'TriggerResult' => 'TriggerResult.php',
    );

    public static function loadClass($class)
    {
        $class = ltrim($class, '\\'); // Fix for bug in PHP 5.3.0 and 5.3.1

        if (isset(self::$classMap[$class])) {
            require dirname(__FILE__).DIRECTORY_SEPARATOR.self::$classMap[$class];
        }
    }
}

spl_autoload_register('PusherAutoloader::loadClass');
