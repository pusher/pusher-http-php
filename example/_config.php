<?php

require "../src/functions.php";

// Ignore on Heroku
PusherREST::configure(
    'api_url' => "http://...:...@api.pusher.com/apps/1234",
    'socket_url' => "ws://...",
);
