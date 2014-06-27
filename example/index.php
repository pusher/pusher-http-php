<?php
require "_config.php";
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
            <h1>Message history</h1>
            <div id="history"></div>
        </div>
        <div class="col-md-6">
            <h1>Message sender</h1>
            <form id="hello" action="trigger.php" method="POST">
                <input name="message" placeholder="message">
                <button type="submit">send</button>
            </form>
        </div>
    </div>
</body>
<?php PusherREST\print_client_setup('/auth.php'); ?>
<script src="//code.jquery.com/jquery.js"></script>
<script>
// $("#hello").submit(function(ev) {
//     ev.preventDefault();
//     $.post('/trigger.php', {message: $('#hello input[name=message]').value()});
// });
    pusher.subscribe('messages', function(event) {
        $('#history').append(event);
    });
</script>
</html>
