<?php

error_reporting(E_ALL);

if (file_exists(__DIR__ . '/config.php') === true) {
    require 'config.php';
} else {
    define('PUSHERAPP_AUTHKEY', getenv('PUSHERAPP_AUTHKEY'));
    define('PUSHERAPP_SECRET', getenv('PUSHERAPP_SECRET'));
    define('PUSHERAPP_APPID', getenv('PUSHERAPP_APPID'));

    define('PUSHERAPP_CLUSTER', getenv('PUSHERAPP_CLUSTER'));

    define('TEST_CHANNEL', getenv('TEST_CHANNEL'));
}
