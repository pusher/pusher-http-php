<?php
/*

This pages sends an event to pusher trough the API to all listening
sockets on the 'messages' channel.

*/
require '_config.php';

// TODO: input sanitization
$channel_name = $_REQUEST['channel_name'];
$message = $_REQUEST['message'];

$ret = $pusher->trigger($channel_name, 'some_event', array('message' => $message));

header('Created', true, 201);
?>
OK
