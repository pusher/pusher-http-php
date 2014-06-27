<?php
/*

  This page is the target of pusher's webhook callback.

  http://pusher.com/docs/webhooks

  TODO: how do you select which key is used ?

 */

require "_config.php";

$events = PusherREST\webhook_events();

// Here, handle the events, like store in the DB.
foreach ($events as &$event) {
    // do something with the event
    switch ($event['name']) {
        case "channel_occupied":
        case "channel_vacated":
        case "member_added":
        case "member_removed":
        case "client_event":
    }
}

header('Content-Type: text/plain');
?>
OK
