<?php
require "_config.php";
header('OK', true, 200);
?>
<!DOCTYPE html>
<head>
    <meta charset="utf-8">
    <title>PusherREST Example</title>
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
</head>
<body class="container">
    <div class="row">
        <div class="col-md-6">
            <h1>Message receiver</h1>
            <div id="history"></div>
        </div>
        <div class="col-md-6">
            <h1>Message sender</h1>
            <form id="hello" action="trigger.php" method="POST">
                <input name="channel_name" value="presence-messages" type="hidden">
                <input name="message" placeholder="message">
                <button type="submit">send</button>
            </form>
        </div>
    </div>
</body>

<script src="//js.pusher.com/2.2/pusher.min.js"></script>
<script>
Pusher.log = console.log.bind(console);
var pusher = new Pusher("<?= $pusher->keyPair()->key ?>", {
    'authEndpoint': "/auth.php"
});
</script>
<script src="//code.jquery.com/jquery.js"></script>
<script>
$("#hello").submit(function(ev) {
    ev.preventDefault();
    $.post('/trigger.php', $('#hello').serialize())
});

var chan = pusher.subscribe('presence-messages');
chan.bind('some_event', function(event) {
    console.log('event', event);
    $('#history').append('some_event: ' + JSON.stringify(event) + '<br>');
});
</script>
</html>
