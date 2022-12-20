# Pusher Channels HTTP PHP Library

[![Tests](https://github.com/pusher/pusher-http-php/actions/workflows/test.yml/badge.svg)](https://github.com/pusher/pusher-http-php/actions/workflows/test.yml) [![Packagist Version](https://img.shields.io/packagist/v/pusher/pusher-php-server)](https://packagist.org/packages/pusher/pusher-php-server) [![Packagist License](https://img.shields.io/packagist/l/pusher/pusher-php-server)](https://packagist.org/packages/pusher/pusher-php-server) [![Packagist Downloads](https://img.shields.io/packagist/dm/pusher/pusher-php-server)](https://packagist.org/packages/pusher/pusher-php-server)

PHP library for interacting with the Pusher Channels HTTP API.

Register at <https://pusher.com> and use the application credentials within your app as shown below.

## Installation

You can get the Pusher Channels PHP library via a composer package called `pusher-php-server`. See <https://packagist.org/packages/pusher/pusher-php-server>

```bash
$ composer require pusher/pusher-php-server
```

Or add to `composer.json`:

```json
"require": {
    "pusher/pusher-php-server": "^7.2"
}
```

then run `composer update`.

## Supported platforms

* PHP - supports PHP versions 7.3, 7.4, 8.0, and 8.1.
* Laravel - version 8.29 and above has built-in support for Pusher Channels as a [Broadcasting backend](https://laravel.com/docs/master/broadcasting).
* Other PHP frameworks - supported provided you are using a supported version of PHP.

## Pusher Channels constructor

Use the credentials from your Pusher Channels application to create a new `Pusher\Pusher` instance.

```php
$app_id = 'YOUR_APP_ID';
$app_key = 'YOUR_APP_KEY';
$app_secret = 'YOUR_APP_SECRET';
$app_cluster = 'YOUR_APP_CLUSTER';

$pusher = new Pusher\Pusher($app_key, $app_secret, $app_id, ['cluster' => $app_cluster]);
```

The fourth parameter is an `$options` array. The additional options are:

* `scheme` - e.g. http or https
* `host` - the host e.g. api.pusherapp.com. No trailing forward slash
* `port` - the http port
* `path` - a prefix to append to all request paths. This is only useful if you
  are running the library against an endpoint you control yourself (e.g. a
  proxy that routes based on the path prefix).
* `timeout` - the HTTP timeout
* `useTLS` - quick option to use scheme of https and port 443.
* `cluster` - specify the cluster where the application is running from.
* `encryption_master_key` - a 32 char long key. This key, along with the
  channel name, are used to derive per-channel encryption keys. Per-channel
  keys are used encrypt event data on encrypted channels.

For example, by default calls will be made over HTTPS. To use plain
HTTP you can set useTLS to false:

```php
$options = [
  'cluster' => $app_cluster,
  'useTLS' => false
];

$pusher = new Pusher\Pusher($app_key, $app_secret, $app_id, $options);
```

## Logging configuration

The recommended approach of logging is to use a
[PSR-3](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md)
compliant logger implementing `Psr\Log\LoggerInterface`. The `Pusher` object
implements `Psr\Log\LoggerAwareInterface`, meaning you call
`setLogger(LoggerInterface $logger)` to set the logger instance.

```php
// where $logger implements `LoggerInterface`

$pusher->setLogger($logger);
```

## Custom Guzzle client

This library uses Guzzle internally to make HTTP calls. You can pass
your own Guzzle instance to the Pusher constructor:

```php
$custom_client = new GuzzleHttp\Client();

$pusher = new Pusher\Pusher(
    $app_key,
    $app_secret,
    $app_id,
    [],
    $custom_client
);
```

This allows you to pass in your own middleware, see the tests for an
[example](tests/acceptance/middlewareTest.php).

## Publishing/Triggering events

To trigger an event on one or more channels use the `trigger` function.

### A single channel

```php
$pusher->trigger('my-channel', 'my_event', 'hello world');
```

### Multiple channels

```php
$pusher->trigger([ 'channel-1', 'channel-2' ], 'my_event', 'hello world');
```

### Batches

It's also possible to send multiple events with a single API call (max 10
events per call on multi-tenant clusters):

```php
$batch = [];
$batch[] = ['channel' => 'my-channel', 'name' => 'my_event', 'data' => ['hello' => 'world']];
$batch[] = ['channel' => 'my-channel', 'name' => 'my_event', 'data' => ['myname' => 'bob']];
$pusher->triggerBatch($batch);
```

### Asynchronous interface

Both `trigger` and `triggerBatch` have asynchronous counterparts in
`triggerAsync` and `triggerBatchAsync`. These functions return [Guzzle
promises](https://github.com/guzzle/promises) which can be chained
with `->then`:

```php
$promise = $pusher->triggerAsync(['channel-1', 'channel-2'], 'my_event', 'hello world');

$promise->then(function($result) {
  // do something with $result
  return $result;
});

$final_result = $promise->wait();
```

### Arrays

Arrays are automatically converted to JSON format:

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

In order to avoid duplicates you can optionally [specify the sender's
socket id](https://pusher.com/docs/channels/server_api/excluding-event-recipients)
while triggering an event:

```php
$pusher->trigger('my-channel', 'event', 'data', ['socket_id' => $socket_id]);
```

```php
$batch = [];
$batch[] = ['channel' => 'my-channel', 'name' => 'my_event', 'data' => ['hello' => 'world'], ['socket_id' => $socket_id]];
$batch[] = ['channel' => 'my-channel', 'name' => 'my_event', 'data' => ['myname' => 'bob'], ['socket_id' => $socket_id]];
$pusher->triggerBatch($batch);
```

### Fetch channel info on publish [[EXPERIMENTAL](https://pusher.com/docs/lab#experimental-program)]

It is possible to request for attributes about the channels that were
published to with the
[`info` param](https://pusher.com/docs/channels/library_auth_reference/rest-api#request):

```php
$result = $pusher->trigger('my-channel', 'my_event', 'hello world', ['info' => 'subscription_count']);
$subscription_count = $result->channels['my-channel']->subscription_count;
```

```php
$batch = [];
$batch[] = ['channel' => 'my-channel', 'name' => 'my_event', 'data' => ['hello' => 'world'], 'info' => 'subscription_count'];
$batch[] = ['channel' => 'presence-my-channel', 'name' => 'my_event', 'data' => ['myname' => 'bob'], 'info' => 'user_count,subscription_count'];
$result = $pusher->triggerBatch($batch);

foreach ($result->batch as $i => $attributes) {
  echo "channel: {$batch[$i]['channel']}, name: {$batch[$i]['name']}";
  if (isset($attributes->subscription_count)) {
    echo ", subscription_count: {$attributes->subscription_count}";
  }
  if (isset($attributes->user_count)) {
    echo ", user_count: {$attributes->user_count}";
  }
  echo PHP_EOL;
}
```

### JSON format

If your data is already encoded in JSON format, you can avoid a second encoding
step by setting the sixth argument true, like so:

```php
$pusher->trigger('my-channel', 'event', 'data', [], true);
```

## Authenticating users

To authenticate users on Pusher Channels on your application, you can use the `authenticateUser` function:

```php
$pusher->authenticateUser('socket_id', 'user-id');
```

For  more information see [authenticating users](https://pusher.com/docs/channels/server_api/authenticating-users/).

## Authorizing Private channels

To authorize your users to access private channels on Pusher, you can use the
`authorizeChannel` function:

```php
$pusher->authorizeChannel('private-my-channel','socket_id');
```

For more information see [authorizing users](https://pusher.com/docs/channels/server_api/authorizing-users/).

## Authorizing Presence channels

Using presence channels is similar to private channels, but you can specify
extra data to identify that particular user:

```php
$pusher->authorizePresenceChannel('presence-my-channel','socket_id', 'user_id', 'user_info');
```

For more information see [authorizing users](https://pusher.com/docs/channels/server_api/authorizing-users/).

## Webhooks

This library provides a way of verifying that webhooks you receive from Pusher
are actually genuine webhooks from Pusher. It also provides a structure for
storing them. A helper method called `webhook` enables this. Pass in the
headers and body of the request, and it'll return a Webhook object with your
verified events. If the library was unable to validate the signature, an
exception is thrown instead.

```php
$webhook = $pusher->webhook($request_headers, $request_body);
$number_of_events = count($webhook->get_events());
$time_received = $webhook->get_time_ms();
```

## End to end encryption

This library supports end to end encryption of your private channels. This
means that only you and your connected clients will be able to read your
messages. Pusher cannot decrypt them. You can enable this feature by following
these steps:

1. You should first set up Private channels. This involves [creating an
   authorization endpoint on your
   server](https://pusher.com/docs/authorizing_users).

2. Next, generate your 32 byte master encryption key, base64 encode it and
   store it securely.  This is secret and you should never share this with
   anyone. Not even Pusher.

   To generate an appropriate key from a good random source, you can use the
   `openssl` command:

   ```sh
   openssl rand -base64 32
   ```

3. Specify your master encryption key when creating your Pusher client:

   ```php
   $pusher = new Pusher\Pusher(
       $app_key,
       $app_secret,
       $app_id,
       [
           'cluster' => $app_cluster,
           'encryption_master_key_base64' => "<your base64 encoded master key>"
       ]
    );
   ```

4. Channels where you wish to use end to end encryption should be prefixed with
   `private-encrypted-`.

5. Subscribe to these channels in your client, and you're done! You can verify
   it is working by checking out the debug console on the
   [https://dashboard.pusher.com/](dashboard) and seeing the scrambled
   ciphertext.

**Important note: This will __not__ encrypt messages on channels that are not
prefixed by `private-encrypted-`.**

**Limitation**: you cannot trigger a single event on a mixture of unencrypted
and encrypted channels in a call to `trigger`, e.g.

```php
$data['name'] = 'joe';
$data['message_count'] = 23;

$pusher->trigger(['channel-1', 'private-encrypted-channel-2'], 'test_event', $data);
```

Rationale: the methods in this library map directly to individual Channels HTTP
API requests. If we allowed triggering a single event on multiple channels
(some encrypted, some unencrypted), then it would require two API requests: one
where the event is encrypted to the encrypted channels, and one where the event
is unencrypted for unencrypted channels.

### Presence example

First set the channel authorization endpoint in your JS app when creating the Pusher object:

```js
var pusher = new Pusher("app_key",
  // ...
  channelAuthorization: {
    endpoint: "/presenceAuth.php",
  },
);
```

Next, create the following in presenceAuth.php:

```php
<?php

header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
  $stmt = $pdo->prepare("SELECT * FROM `users` WHERE id = :id");
  $stmt->bindValue(':id', $_SESSION['user_id'], PDO::PARAM_INT);
  $stmt->execute();
  $user = $stmt->fetch();
} else {
  die(json_encode('no-one is logged in'));
}

$pusher = new Pusher\Pusher($key, $secret, $app_id);
$presence_data = ['name' => $user['name']];

echo $pusher->authorizePresenceChannel($_POST['channel_name'], $_POST['socket_id'], $user['id'], $presence_data);
```

Note: this assumes that you store your users in a table called `users` and that
those users have a `name` column. It also assumes that you have a login
mechanism that stores the `user_id` of the logged in user in the session.

## Application State Queries

### Get information about a channel

```php
$pusher->getChannelInfo($name);
```

It's also possible to get information about a channel from the Channels HTTP API.

```php
$info = $pusher->getChannelInfo('channel-name');
$channel_occupied = $info->occupied;
```

For [presence channels](https://pusher.com/docs/presence_channels) you can also
query the number of distinct users currently subscribed to this channel (a
single user may be subscribed many times, but will only count as one):

```php
$info = $pusher->getChannelInfo('presence-channel-name', ['info' => 'user_count']);
$user_count = $info->user_count;
```

If you have enabled the ability to query the `subscription_count` (the number
of connections currently subscribed to this channel) then you can query this
value as follows:

```php
$info = $pusher->getChannelInfo('presence-channel-name', ['info' => 'subscription_count']);
$subscription_count = $info->subscription_count;
```

### Get a list of application channels

```php
$pusher->getChannels();
```

It's also possible to get a list of channels for an application from the
Channels HTTP API.

```php
$result = $pusher->getChannels();
$channel_count = count($result->channels); // $channels is an Array
```

### Get a filtered list of application channels

```php
$pusher->getChannels(['filter_by_prefix' => 'some_filter']);
```

It's also possible to get a list of channels based on their name prefix. To do
this you need to supply an `$options` parameter to the call. In the following
example the call will return a list of all channels with a `presence-` prefix.
This is idea for fetching a list of all presence channels.

```php
$results = $pusher->getChannels(['filter_by_prefix' => 'presence-']);
$channel_count = count($result->channels); // $channels is an Array
```

This can also be achieved using the generic `pusher->get` function:

```php
$pusher->get('/channels', ['filter_by_prefix' => 'presence-']);
```

### Get a list of application channels with subscription counts

The HTTP API returning the channel list does not support returning the
subscription count along with each channel. Instead, you can fetch this data by
iterating over each channel and making another request. Be warned: this
approach consumes (number of channels + 1) messages!

```php
<?php
$subscription_counts = [];
foreach ($pusher->getChannels()->channels as $channel => $v) {
  $subscription_counts[$channel] =
    $pusher->getChannelInfo(
      $channel, ['info' => 'subscription_count']
    )->subscription_count;
}
var_dump($subscription_counts);
```

### Get user information from a presence channel

```php
$results = $pusher->getPresenceUsers('presence-channel-name');
$users_count = count($results->users); // $users is an Array
```

This can also be achieved using the generic `pusher->get` function:

```php
$response = $pusher->get('/channels/presence-channel-name/users');
```

The `$response` is in the format:

```php
Array (
	[body] => {"users":[{"id":"a_user_id"}]}
	[status] => 200
	[result] => Array (
		[users] => Array (
			[0] => Array (
				[id] => a_user_id
			),
			/* Additional users */
		)
	)
)
```

### Generic get function

```php
$pusher->get($path, $params);
```

Used to make `GET` queries against the Channels HTTP API. Handles authentication.

Response is an associative array with a `result` index. The contents of this
index is dependent on the HTTP method that was called. However, a `status`
property to allow the HTTP status code is always present and a `result`
property will be set if the status code indicates a successful call to the API.

```php
$response = $pusher->get('/channels');
$http_status_code = $response['status'];
$result = $response['result'];
```

## Running the tests

Requires [phpunit](https://github.com/sebastianbergmann/phpunit).

* Run `composer install`
* Go to the `tests` directory
* Rename `config.example.php` and replace the values with valid Channels
  credentials **or** create environment variables.
* Some tests require a client to be connected to the app you defined in the
  config; you can do this by opening
  https://dashboard.pusher.com/apps/<YOUR_TEST_APP_ID>/getting_started in the
  browser
* From the root directory of the project, execute `composer exec phpunit` to
  run all the tests.

## License

Copyright 2014, Pusher. Licensed under the MIT license:
https://www.opensource.org/licenses/mit-license.php

Copyright 2010, Squeeks. Licensed under the MIT license:
https://www.opensource.org/licenses/mit-license.php
