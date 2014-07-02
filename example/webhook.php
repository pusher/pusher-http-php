<?php
/*

  This page is the target of pusher's webhook callback.

  http://pusher.com/docs/webhooks

 */

require "_config.php";

$wh = $pusher->webhook();

if (!$wh->valid()) {
  header('Status: 401 Unauthorized');
  exit;
}

// Here, handle the events store in the DB.
foreach ($wh->events() as &$event) {
    // do something with the event
    switch ($event['name']) {
        case "channel_occupied":
        case "channel_vacated":
        case "member_added":
        case "member_removed":
        case "client_event":
          var_dump($event);
          break;
        default:
          print("Unknown event type" . $event["name"]);
    }
}

header('Content-Type: text/plain');
?>
OK
