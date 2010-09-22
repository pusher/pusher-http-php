#!/usr/bin/env php
<?php
	require 'lib/Test.php';

	define('PUSHERAPP_AUTHKEY', getenv('PUSHERAPP_AUTHKEY'));
	define('PUSHERAPP_SECRET' , getenv('PUSHERAPP_SECRET'));
	define('PUSHERAPP_APPID'  , getenv('PUSHERAPP_APPID'));

	if(PUSHERAPP_AUTHKEY && PUSHERAPP_SECRET && PUSHERAPP_APPID)
	{
		plan(4);
	}
	else
	{
		plan('skip_all', 'Environment vars needed for live tests not defined.');
	}
	
	require_ok('lib/Pusher.php');

	$pusher = new Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, true);
	ok($pusher, 'Created new Pusher object');

	$string_trigger = $pusher->trigger('test_channel', 'my_event', date('l jS \of F Y h:i:s A'));
	ok($string_trigger, 'Trigger with string payload');

	$structure_trigger = $pusher->trigger('test_channel', 'my_event', array('time' => date('U') ));
	ok($structure_trigger, 'Trigger with structured payload');

?>
