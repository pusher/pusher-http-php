#!/usr/bin/env php
<?php
	require 'lib/Test.php';

	plan(3);
	
	require_ok('lib/Pusher.php');

	$pusher = new Pusher('thisisaauthkey', 'thisisasecret', 1, true);
	ok($pusher, 'Created new Pusher object');

	$socket_auth = $pusher->socket_auth('testing_pusher-php', 'testing_socket_auth');
	is($socket_auth, '{"auth":"thisisaauthkey:ee548cf60217ed18281da39a8eb23609105f1bde29372650cb67bd91c284aae1"}', 'Socket Auth');
?>

