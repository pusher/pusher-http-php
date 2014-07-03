<?php
/*

  This page returns a JSON delegated auth for the pusher-js library

 */

require "_config.php";

// TODO: Add XSS check here
// TODO: Check user auth here

$socket_id = $_POST['socket_id'];
$channel_name = $_POST['channel_name'];
$channel_data = array(
    'user_id' => rand()
);

$data = $pusher->authenticate($socket_id, $channel_name, $channel_data);

header('Content-Type: application/json');
print($data);
