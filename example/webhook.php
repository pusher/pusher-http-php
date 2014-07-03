<?php
/*

  This page is the target of pusher's webhook callback.

  http://pusher.com/docs/webhooks

 */

require "_config.php";

$wh = $pusher->webhook();

if (!$wh->valid()) {
    header('Unauthorized', true, 401);
    exit;
}

$events = $wh->events();

// Here, handle the events store in the DB.
foreach ($events as &$event) {
    // do something with the event
    switch ($event['name']) {
        case "channel_occupied":
        case "channel_vacated":
        case "member_added":
        case "member_removed":
        case "client_event":
            // Do something with the event (eg: update the DB)
            var_dump($event);
            break;
        default:
            var_dump("Fail");
    }
}

header('Content-Type: text/plain');
?>
OK
