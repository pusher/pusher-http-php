# Pusher HTTP PHP library

[![Build Status](https://travis-ci.org/pusher/pusher-http-php.svg?branch=master)](https://travis-ci.org/pusher/pusher-http-php)

PHP library for interacting with the Pusher HTTP API.

In order to use this library, you need to have an account on
<https://pusher.com>. After registering, you will need the application
credentials for your app.

## Feature Support

*Provide information regarding the features that the library supports. What it does and what it doesn't. This section can also form a table of contents to the information within the README*

Feature                                    | Supported
-------------------------------------------| :-------:
Trigger event on single channel            | *&#10004;*
Trigger event on multiple channels         | *&#10004;*
Excluding recipients from events           | *&#10004;*
Authenticating private channels            | *&#10004;*
Authenticating presence channels           | *&#10004;*
Get the list of channels in an application | *&#10004;*
Get the state of a single channel          | *&#10004;*
Get a list of users in a presence channel  | *&#10004;*
WebHook validation                         | *&#10004;*
Debugging & Logging                        | *&#10008;*
HTTPS                                      | *&#10004;*
HTTP Proxy configuration                   | *&#10004;*
Cluster configuration                      | *&#10004;*

Libraries can also offer additional helper functionality to ensure interactions with the HTTP API only occur if they will not be rejected e.g. [channel naming conventions][channel-names]. For information on the helper functionality that this library supports please see the **Helper Functionality** section.

## Installation

Use [composer](http://getcomposer.org) to install this bundle.

```
$ composer require pusher/pusher-http
```

PHP 5.3+ is required to use this library.

## Configuration

```php
$pusher = Pusher::fromEnv();
```

Or with App ID, key and secret:

```php
$pusher = new Pusher::Client(array(
  appId   => 'APP_ID',
  key     => 'APP_KEY',
  secret  => 'SECRET_KEY',
  cluster => 'mt1',
));
```

### Additional options

The library also supports configuration specialization by passing an options
array to the various constructors:

```php
$pusher = Pusher::fromEnv('PUSHER_URL', array(
  'http_proxy' => 'http://foobar',
  'timeout'    => '5s',
));
```

## Usage


### Triggering events


Compatibility
-------------

This library follows [Semantic Versioning](http://semver.org).


Publishing/Triggering events
----------------------------

To trigger an event on one or more channels use the `trigger` function.

### A single channel

```php
$pusher->trigger( 'my-channel', 'my_event', 'hello world' );
```

### Multiple channels

```php
$pusher->trigger( array('channel-1', 'channel-2'), 'my_event', 'hello world' );
```

### Arrays

Objects are automatically converted to JSON format:

```php
$event_data = array('name' => 'joe', 'message_count' => 23);
$pusher->trigger('my_channel', 'my_event', $event_data);
```

The output of this will be:

```json
{"name": "joe", "message_count": 23}
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

Used to make `GET` queries against the Pusher HTTP API. Handles authentication.

Response is an associative array with a `result` index. The contents of this index is dependent on the HTTP method that was called. However, a `status` property to allow the HTTP status code is always present and a `result` property will be set if the status code indicates a successful call to the API.

    $response = $pusher->get( '/channels' );
    $http_status_code = $response[ 'status' ];
    $result = $response[ 'result' ];

### Get information about a channel

    get_channel_info( $name )

It's also possible to get information about a channel from the Pusher HTTP API.

    $info = $pusher->get_channel_info('channel-name');
    $channel_occupied = $info->occupied;

This can also be achieved using the generic `pusher->get` function:

    pusher->get( '/channels/channel-name' );

### Get a list of application channels

    get_channels()

It's also possible to get a list of channels for an application from the Pusher HTTP API.

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

