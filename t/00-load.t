#!/usr/bin/env php
<?php
	require 'lib/Test.php';

	plan(1);
	diag('Testing Pusher PHP');
	require_ok('lib/Pusher.php');
?>
