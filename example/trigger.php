<?php
/*

This pages sends an event to pusher trough the API to all listening
sockets on the 'messages' channel.

*/
require "_config.php";

// TODO: input sanitization
$channel_name = $_POST['channel_name'];
$message = $_POST['message'];

$ret = $pusher->trigger($channel_name, 'some_event', array('message' => $message));

header("Status: 201 Created");

?>
OK
