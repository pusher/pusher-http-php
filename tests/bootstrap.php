<?php

error_reporting(E_ALL);

$dir = dirname(__FILE__);
$config_path = $dir.'/config.php';
if (file_exists($config_path) === true) {
    require_once $config_path;
} else {
    define('PUSHERAPP_AUTHKEY', getenv('PUSHERAPP_AUTHKEY'));
    define('PUSHERAPP_SECRET', getenv('PUSHERAPP_SECRET'));
    define('PUSHERAPP_APPID', getenv('PUSHERAPP_APPID'));

    define('PUSHERAPP_HOST', getenv('PUSHERAPP_HOST'));
}
