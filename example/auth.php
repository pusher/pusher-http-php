<?php
/*

This page returns a JSON delegated auth for the pusher-js library

*/

require "_config.php";

// TODO: Add XSS check here

// TODO: Check auth here

$socket_id = PusherREST::socket_id($_POST['socket_id']);
$channel = PusherREST::channel($_POST['channel']);
$user_data = PusherREST::user_data($_POST['user_data']);

$auth = PusherREST::auth($socket_id, $channel, $user_data);

header('Content-Type: application/json');
print($auth);
