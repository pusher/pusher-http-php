<?php namespace Pusher;

// re-define some function in Pusher namespace for easier testing
function curl_init() { return 'curl_mock'; }
function curl_setopt($client, $optname, $value) { return ClientTest::handleCurlSetOpt($client, $optname, $value); }
function curl_exec($client) { return 'executed'; }
function curl_getinfo($client, $optname) { return ClientTest::handleCurlGetInfo($client, $optname); }
function time() { return 1000; }
function hash_hmac($algo, $data, $secret, $rawOutput) { return 'hmac_hash'; }


require './vendor/autoload.php';
