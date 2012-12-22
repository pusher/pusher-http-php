# Pusher PHP Library

This is a PHP library to the Pusher API (http://pusher.com).

## Basic Usage Example

    require('Pusher.php');
    $pusher = new Pusher( $key, $secret, $app_id );

    $pusher->trigger( 'my-channel', 'my-event', array( 'message' => 'hello world' ) );
    
## Configuration

After registering at <http://pusher.com> configure your app with the security credentials.

## Pusher constructor

Use the credentials from your Pusher application to create a new `Pusher` instance.

    $app_id = 'YOUR_APP_ID';
    $app_key = 'YOUR_APP_KEY';
    $app_secret = 'YOUR_APP_SECRET';

    $pusher = new Pusher( $app_key, $app_secret, $app_id );

By default calls will be made over a non-encrypted connection. To change this to make calls over HTTPS:

    $pusher = new Pusher( $app_key, $app_secret, $app_id, false, 'https://api.pusherapp.com', 443 );

## Publishing/Triggering events

To trigger an event on one or more channels use the `trigger` function.

### A single channel
    
    $pusher->trigger( 'my-channel', 'my_event', 'hello world' );

### Multiple channels

    $pusher->trigger( [ 'channel-1', 'channel-2' ], 'my_event', 'hello world' );

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

### Logging

You can pass an object with a `log` function to the `pusher->set_logger` function so that you can log information from the library.

    class MyLogger {
        public function log( $msg ) {
            print_r( $msg . "\n" );
        }
    }
    
    $pusher->set_logger( new MyLogger() );

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

## Application State Queries

### Get information about a channel

    get_channel_info( $name )

It's also possible to get information about a channel from the Pusher REST API.

    $info = $pusher->get_channel_info('channel-name');
    $channel_occupied = $info->occupied;

This can also be achieved using the generic `pusher->get` function:

    pusher->get( '/channels/channel-name' );    
    
### Get a list of application channels

    get_channels()

It's also possible to get a list of channels for an application from the Pusher REST API.

    $result = $pusher->get_channels();
    $channel_count = count($result->channels); // $channels is an Array

This can also be achieved using the generic `pusher->get` function:

    pusher->get( '/channels' );
  
### Get a filtered list of application channels

    get_channels( array( 'filter_by_prefix' => 'some_filter' )

It's also possible to get a list of channels based on their name prefix. To do this you need to supply an $options parameter to the call. In the following example the call will return a list of all channels with a 'presence-' prefix. This is idea for fetching a list of all presence channels.

    $results = $pusher->get_channels( array( 'filter_by_prefix' => 'presence-') );
    $channel_count = count($result->channels); // $channels is an Array

This can also be achieved using the generic `pusher->get` function:

    pusher->get( '/channels', 'filter_by_prefix' => 'presence-') );

## Generic get function

    pusher-get( $path, $params ) 

Used to make `GET` queries against the Pusher REST API. Handles authentication.

Response is an associative array with a `result` index. The contents of this index is dependent on the REST method that was called. However, a `status` property to allow the HTTP status code is always present and a `result` property will be set if the status code indicates a successful call to the API.

    $response = $pusher->get( '/channels' );
    $http_status_code = $response[ 'status' ];
    $result = $response[ 'result' ];
    
## Running the tests

Requires [phpunit](https://github.com/sebastianbergmann/phpunit/).

* Got to the `tests` directory
* Rename `config.example.php` and replace the values with valid Pusher credentials **or** create environment variables.
* Execute `phpunit .` to run all the tests.
    
## License

Copyright 2010, Squeeks. Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php 

