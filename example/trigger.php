<?php

require "_config.php";

$ret = PusherREST\trigger('some_channel', 'some_event', array('foo' => 'bar'));

header("Status: 201 Created");

var_dump($ret);

?>
OK
