Pusher PHP Library
==================

This is a very simple PHP library to the Pusher API (http://pusherapp.com).
Using it is easy as pie:

    require('Pusher.php');

    $pusher = new Pusher($key, $secret, $app_id, $channel);
    $pusher->trigger('my_event', 'hello world');

Debugging
---------
You can either turn on debugging by setting the third argument like so, to true:

    $pusher->trigger('event', 'data', true)

or with all requests:

    $pusher = new Pusher($key, $secret, $app_id, $channel, true);

On failed requests, this will return the server's response, instead of false.

Channels
---------
You can specify the channel either while constructing the Pusher object as described above, or while triggering an event:

$pusher->trigger('event', 'data', null, 'channel');

Socket id
---------
In order to avoid duplicates you can optionally specify the sender's socket id while triggering an event (http://pusherapp.com/docs/duplicates):

$pusher->trigger('event','data','socket_id');

License
-------
Copyright 2010, Squeeks. Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php 

