<?php
/*

This pages sends an event to pusher trough the API to all listening
sockets on the 'messages' channel.

 */
require "_config.php";

$ret = $pusher->trigger('messages', 'some_event', array('foo' => 'bar'));

header("Status: 201 Created");

var_dump($ret);
?>
OK
