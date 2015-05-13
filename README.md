# Pusher PHP Library

[![Build Status](https://travis-ci.org/pusher/pusher-http-php.svg)](https://travis-ci.org/pusher/pusher-http-php)

PHP library for interacting with the Pusher HTTP API.

Register at <https://pusher.com> and use the application credentials within your app as shown below.

## Installation

You can get the Pusher PHP library via a composer package called `pusher-php-server`. See <https://packagist.org/packages/pusher/pusher-php-server>

```
php composer.phar require pusher/pusher-php-server
```

Or you can clone or download the library files. When using the library this way, you need to require the ``lib/autoload.php`` file of the library to register the autoloader (this is not needed when using Composer as it handles the autoloading).

**We recommend you [use composer](https://getcomposer.org/).**


## Pusher constructor

Use the credentials from your Pusher application to create a new `Pusher` instance.

```php
$app_id = 'YOUR_APP_ID';
$app_key = 'YOUR_APP_KEY';
$app_secret = 'YOUR_APP_SECRET';

$pusher = new Pusher( $app_key, $app_secret, $app_id );
```

A forth parameter `$options` parameter can also be passed. The available options are:

* `scheme` - e.g. http or https
* `host` - the host e.g. api.pusherapp.com. No trailing forward slash.
* `port` - the http port
* `timeout` - the HTTP timeout
* `encrypted` - quick option to use scheme of https and port 443.

For example, by default calls will be made over a non-encrypted connection. To change this to make calls over HTTPS use:

```php
$pusher = new Pusher( $app_key, $app_secret, $app_id, array( 'encrypted' => true ) );
```

*Note: The `$options` parameter was introduced in version 2.2.0 of the library.
Previously additional parameters could be passed for each option, but this was
becoming unwieldy. However, backwards compatibility has been maintained.*

## Publishing/Triggering events

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
$array['name'] = 'joe';
$array['message_count'] = 23;

$pusher->trigger('my_channel', 'my_event', $array);
```

The output of this will be:

```json
"{'name': 'joe', 'message_count': 23}"
```

### Socket id

In order to avoid duplicates you can optionally specify the sender's socket id while triggering an event ([http://pusherapp.com/docs/duplicates](http://pusherapp.com/docs/duplicates)):

```php
$pusher->trigger('my-channel','event','data','socket_id');
```

### JSON format

If your data is already encoded in JSON format, you can avoid a second encoding step by setting the sixth argument true, like so:

```php
$pusher->trigger('my-channel', 'event', 'data', null, false, true)
```

### Event Buffer

Version 3.0.0 of the library introduced support for [event buffering](https://pusher.com/docs/event_buffer). The purpose of this functionality is to ensure that events that are triggered whilst a client is offline for a short period of time will still be delivered.

*Note: this requires your Pusher application to be on a cluster that has the Event Buffer capability*

As part of this the `trigger` function now returns a set of `event_id` values for each event triggered on a channel. These can then be used by the client upon subscribing to a channel to tell the Pusher service the last event it has received. If additional events have been triggered after that event ID the service has the opportunity to provide the client with those IDs.

For detailed information please see the [Event Buffer Documentation](https://pusher.com/docs/event_buffer).

The event ID values are accessed via a `PusherTriggerResult` object that is returned from the `trigger` call.

```php
// Trigger on single channel
$triggerResult = $pusher->trigger('ch1', 'my-event', ['some' => 'data']);
$eventId = $triggerResult->eventIds['ch1'];

// Trigger on multiple channels
$multiTriggerResult = $pusher->trigger(['ch1', 'ch2', 'ch3'], 'my-event', ['some' => 'data']);
$ch1EventId = $triggerResult->eventIds['ch1'];
$ch2EventId = $triggerResult->eventIds['ch2'];
$ch3EventId = $triggerResult->eventIds['ch3'];
```

## Authenticating Private channels

To authorise your users to access private channels on Pusher, you can use the socket_auth function:

```php
$pusher->socket_auth('my-channel','socket_id');
```

## Authenticating Presence channels

Using presence channels is similar to private channels, but you can specify extra data to identify that particular user:

```php
$pusher->presence_auth('my-channel','socket_id', 'user_id', 'user_info');
```

### Presence example

First set this variable in your JS app:

```php
Pusher.channel_auth_endpoint = '/presence_auth.php';
```

Next, create the following in presence_auth.php:

```php
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
```

Note: this assumes that you store your users in a table called `users` and that those users have a `name` column. It also assumes that you have a login mechanism that stores the `user_id` of the logged in user in the session.

## Application State Queries

### Generic get function

```php
$pusher->get( $path, $params );
```

Used to make `GET` queries against the Pusher REST API. Handles authentication.

Response is an associative array with a `result` index. The contents of this index is dependent on the REST method that was called. However, a `status` property to allow the HTTP status code is always present and a `result` property will be set if the status code indicates a successful call to the API.

```php
$response = $pusher->get( '/channels' );
$http_status_code = $response[ 'status' ];
$result = $response[ 'result' ];
```

### Get information about a channel

```php
$pusher->get_channel_info( $name );
```

It's also possible to get information about a channel from the Pusher REST API.

```php
$info = $pusher->get_channel_info('channel-name');
$channel_occupied = $info->occupied;
```

This can also be achieved using the generic `pusher->get` function:

```php
$pusher->get( '/channels/channel-name' );
```

### Get a list of application channels

```php
$pusher->get_channels()
```

It's also possible to get a list of channels for an application from the Pusher REST API.

```php
$result = $pusher->get_channels();
$channel_count = count($result->channels); // $channels is an Array
```

This can also be achieved using the generic `pusher->get` function:

```php
$pusher->get( '/channels' );
```

### Get a filtered list of application channels

```php
$pusher->get_channels( array( 'filter_by_prefix' => 'some_filter' ) )
```

It's also possible to get a list of channels based on their name prefix. To do this you need to supply an $options parameter to the call. In the following example the call will return a list of all channels with a 'presence-' prefix. This is idea for fetching a list of all presence channels.

```php
$results = $pusher->get_channels( array( 'filter_by_prefix' => 'presence-') );
$channel_count = count($result->channels); // $channels is an Array
```

This can also be achieved using the generic `pusher->get` function:

```php
$pusher->get( '/channels', array( 'filter_by_prefix' => 'presence-' ) );
```

### Get user information from a presence channel

```php
$response = $pusher->get( '/channels/presence-channel-name/users' )
```

The `$response` is in the format:

```php
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

## Debugging & Logging

The best way to debug your applications interaction with server is to set a logger
for the library so you can see the internal workings within the library and interactions
with the Pusher service.

You set up logging by passing an object with a `log` function to the `pusher->set_logger`
function:

```php
class MyLogger {
  public function log( $msg ) {
    print_r( $msg . "\n" );
  }
}

$pusher->set_logger( new MyLogger() );
```

If you use the above example in code executed from the console/terminal the debug
information will be output there. If you use this within a web app then the output
will appear within the generated app output e.g. HTML.

## Running the tests

Requires [phpunit](https://github.com/sebastianbergmann/phpunit/).

* Got to the `tests` directory
* Rename `config.example.php` and replace the values with valid Pusher credentials **or** create environment variables.
* Execute `phpunit .` to run all the tests.

## Framework Integrations
- **Laravel 4** - https://github.com/artdarek/pusherer
- **Laravel 5** - https://github.com/vinkla/pusher

## License

Copyright 2014, Pusher. Licensed under the MIT license:
http://www.opensource.org/licenses/mit-license.php

Copyright 2010, Squeeks. Licensed under the MIT license:
http://www.opensource.org/licenses/mit-license.php
