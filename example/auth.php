<?php
/*

This page returns a JSON delegated auth for the pusher-js library

*/

require "_config.php";

// TODO: Add XSS check here

// TODO: Check user auth here

$data = PusherREST::delegated_auth($_POST);

header('Content-Type: application/json');
print($data);
