<?php

require "_config.php"

PusherREST::trigger('some_channel', 'some_event', array('foo' => 'bar'));

?>
OK
