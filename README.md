# Pusher PHP Library

This is a very simple PHP library to the Pusher API (http://pusher.com).
Using it is easy as pie:

    require('Pusher.php');
    $pusher = new Pusher($key, $secret, $app_id);
    
If you prefer to use the Singleton pattern usage is similar, but like this:

    require('Pusher.php');
    $pusher = PusherInstance::get_pusher();
    
Then call the appropriate function.

## Trigger

To trigger an event on a channel use the `trigger` function.
    
    $pusher->trigger('my-channel', 'my_event', 'hello world');

Note: You need to set your API information in Pusher.php

### Arrays

Objects are automatically converted to JSON format:

    $array['name'] = 'joe';
    $array['message_count'] = 23;

    $pusher->trigger('my_channel', 'my_event', $array);

The output of this will be:

    "{'name': 'joe', 'message_count': 23}"

### Socket id

In order to avoid duplicates you can optionally specify the sender's socket id while triggering an event (http://pusherapp.com/docs/duplicates):

    $pusher->trigger('my-channel','event','data','socket_id');

### Debugging

You can either turn on debugging by setting the fifth argument to true, like so:

    $pusher->trigger('my-channel', 'event', 'data', null, true)

or with all requests:

    $pusher = new Pusher($key, $secret, $app_id, true);

On failed requests, this will return the server's response, instead of false.

### JSON format

If your data is already encoded in JSON format, you can avoid a second encoding step by setting the sixth argument true, like so:

	$pusher->trigger('my-channel', 'event', 'data', null, false, true)

## Authenticating Private channels

To authorise your users to access private channels on Pusher, you can use the socket_auth function:

    $pusher->socket_auth('my-channel','socket_id');

## Authenticating Presence channels

Using presence channels is similar to private channels, but you can specify extra data to identify that particular user:

    $pusher->presence_auth('my-channel','socket_id', 'user_id', 'user_info');

### Presence example

First set this variable in your JS app:

    Pusher.channel_auth_endpoint = '/presence_auth.php';

Next, create the following in presence_auth.php:

    <?php
    header('Content-Type: application/json');
    if ($_SESSION['user_id']){
      $sql = "SELECT * FROM `users` WHERE id='$_SESSION[user_id]'";
      $result = mysql_query($sql,$mysql);
      $user = mysql_fetch_assoc($result);
    } else {
      die('aaargh, no-one is logged in')
    }
    
    $pusher = new Pusher($key, $secret, $app_id);
    $presence_data = array('name' => $user['name']);
    echo $pusher->presence_auth($_POST['channel_name'], $_POST['socket_id'], $user['id'], $presence_data);
    ?>

Note: this assumes that you store your users in a table called `users` and that those users have a `name` column. It also assumes that you have a login mechanism that stores the `user_id` of the logged in user in the session.

## Channel Queries

### Get information about a channel

    get_channel_info( $name )

It's also possible to get information about a channel from the Pusher REST API.

    $info = $pusher->get_channel_info('channel-name');
    $channel_occupied = $info->occupied;
    
### Get a list of application channels

    get_channels()

It's also possible to get a list of channels for an application from the Pusher REST API.

    $result = $pusher->get_channels();
    $channel_count = count($result->channels); // $channels is an Array
    
  
### Get a filtered list of application channels

    get_channels( array( 'filter_by_prefix' => 'some_filter' )

It's also possible to get a list of channels based on their name prefix. To do this you need to supply an $options parameter to the call. In the following example the call will return a list of all channels with a 'presence-' prefix. This is idea for fetching a list of all presence channels.

    $results = $pusher->get_channels( array( 'filter_by_prefix' => 'presence-') );
    $channel_count = count($result->channels); // $channels is an Array
    
## Running the tests

Requires [phpunit](https://github.com/sebastianbergmann/phpunit/).

* Got to the `tests` directory
* Rename `config.example.php` and replace the values with valid Pusher credentials **or** create environment variables.
* Execute `phpunit .` to run all the tests.
    
## License

Copyright 2010, Squeeks. Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php 

