<?php
/*

  This page returns a JSON delegated auth for the pusher-js library

 */

require "_config.php";

// TODO: Add XSS check here
// TODO: Check user auth here

$socket_id = $_POST['socket_id'];
$channel = $_POST['channel'];
$channel_data = $_POST['channel_data'];

$data = $pusher->authenticate($socket_id, $channel, $channel_data);

header('Content-Type: application/json');
print($data);
