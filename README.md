Puser REST library for PHP5
===========================

**STATUS: WORK IN PROGRESS. See [TODO](TODO.md)**

This is the next-gen official client library for our REST API.
See http://pusher.com/docs/rest_api for the documentation of the API.

Installation
------------

Use [composer](http://getcomposer.org) to install this bundle.

```
$ composer require pusher/pusher-rest
```

Usage
-----

```php
$pusher = new PusherREST\Client(getenv('PUSHER_URL'));
$pusher->trigger('channel_name', 'event_name', array('my' => 'data'));
```

Configuration
-------------

```php

```

Client authentication
---------------------


Incoming WebHooks
-----------------



Compatibility
-------------

This library follows [Semantic Versioning](http://semver.org).


Framework integration
---------------------

http://www.sitepoint.com/best-php-frameworks-2014/

Laravel, Phalcon, Symphony2

Publishing/Triggering events
----------------------------

To trigger an event on one or more channels use the `trigger` function.

### A single channel

```php
$pusher->trigger( 'my-channel', 'my_event', 'hello world' );
```

### Multiple channels

```php
$pusher->trigger( [ 'channel-1', 'channel-2' ], 'my_event', 'hello world' );
```

### Arrays

Objects are automatically converted to JSON format:

```php
$event_data = array('name' => 'joe', 'message_count' => 23);
$pusher->trigger('my_channel', 'my_event', $event_data);
```

The output of this will be:

```json
{'name': 'joe', 'message_count': 23}
```

### Socket id

In order to avoid duplicates you can optionally specify the sender's socket id while triggering an event ( http://pusher.com/docs/duplicates ):

```php
$pusher->trigger('my-channel','event','data','socket_id');
```

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

## Generic get function

    pusher->get( $path, $params )

Used to make `GET` queries against the Pusher REST API. Handles authentication.

Response is an associative array with a `result` index. The contents of this index is dependent on the REST method that was called. However, a `status` property to allow the HTTP status code is always present and a `result` property will be set if the status code indicates a successful call to the API.

    $response = $pusher->get( '/channels' );
    $http_status_code = $response[ 'status' ];
    $result = $response[ 'result' ];

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

    get_channels( array( 'filter_by_prefix' => 'some_filter' ) )

It's also possible to get a list of channels based on their name prefix. To do this you need to supply an $options parameter to the call. In the following example the call will return a list of all channels with a 'presence-' prefix. This is idea for fetching a list of all presence channels.

    $results = $pusher->get_channels( array( 'filter_by_prefix' => 'presence-') );
    $channel_count = count($result->channels); // $channels is an Array

This can also be achieved using the generic `pusher->get` function:

    $pusher->get( '/channels', array( 'filter_by_prefix' => 'presence-' ) );

### Get user information from a presence channel

    $response = $pusher->get( '/channels/presence-channel-name/users' )

The `$response` is in the format:

```
Array
(
    [body] => {"users":[{"id":"a_user_id"}]}
    [status] => 200
    [result] => Array
        (
            [users] => Array
                (
                    [0] => Array
                        (
                            [id] => a_user_id
                        )
                    /* Additional users */
                )
        )
)
```

## License

Copyright 2014 Pusher Ltd. - distributed under the MIT license.

