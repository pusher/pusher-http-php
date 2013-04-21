# Pusher PHP Library

Version 3.0.0

## Introduction

This is the official Pusher PHP client for interacting with the REST Pusher API.

## Dependencies

* [Guzzle library](http://guzzlephp.org): >= 3.4

## Installation

We recommend you to use Composer to install PHP Pusher client. Just add the following line into your `composer.json` file:

```json
{
    require: {
        "pusher/pusher-php-server": "3.*"
    }
}
```

Then, update your dependencies by typing: `php composer.phar update`.

## Tutorial

Pusher PHP client is separated into two ways: the client object (`Pusher\Client\PusherClient` class) which allows
to execute Guzzle commands manually, and the service object (`Pusher\Service\PusherService` class) which is a thin
layer around the client that aims to simplify usage and error handling.

This is how you create a Pusher service:

```php
use Pusher\Client\Credentials;
use Pusher\Client\PusherClient;
use Pusher\Service\PusherService;

$credentials = new Credentials('application-id', 'key', 'secret');
$client      = new PusherClient($credentials);
$service     = new PusherService($client);
```

Once you have access to the service, you can perform any operations.

### Triggering events

To trigger an event to one or more channels, use the `trigger` method. Second parameter can either be a single channel (string),
or multiple channels (an array of strings):

```php
// Single channel
$service->trigger('my-event', 'my-channel-1', array('key' => 'value'));

// Multiplie channels
$service->trigger('my-event', array('my-channel-1', 'my-channel-2'), array('key' => 'value'));
```

`trigger` method also supports a fourth parameter, which is the socket id to exclude a specific socket from receiving
the message ([more information here](http://pusher.com/docs/server_api_guide/server_excluding_recipients)):

```php
// Exclude socket '1234.1234'
$service->trigger('my-event', 'my-channel-1', array('key' => 'value'), '1234.1234');
```

Finally, `trigger` method also supports a fifth parameter which is used to make an asynchronous trigger. This means
that it immediately returns to the client, without waiting for the response. By default, all trigger requests are
done *synchronously*:

```php
// Force the trigger to be asynchronous
$service->trigger('my-event', 'my-channel-1', array('key' => 'value'), '', true);
```

Pusher service also provides a shortcut for doing asynchronous requests with the `triggerAsync` method, as shown above:

```php
$service->triggerAsync('my-event', 'my-channel-1', array('key' => 'value'));
```

### Channel(s) information

You can fetch information about a single channel using the `getChannelInfo` method, with an optional array of information
you want to retrieve (currently, Pusher API only supports *user_count* and *subscription_count* values:

```php
$result = $service->getChannelInfo('my-channel', array('user_count'));
```

You can use the method `getChannelsInfo` to get information about multiple channels, optionally filtered by name. Like
`getChannelInfo`, this method accepts an optional second parameter which is an array of information to retrieve.

```php
// Get information about all channels whose name begins by 'presence-'
$result = $service->getChannelsInfo('presence-');
```

### Presence channel users

You can retrieve all the users in a presence channel user using the `getPresenceUsers` method:

```php
$result = $service->getPresenceUsers('presence-foobar');
```

### Authenticate private channels

To authenticate a user against a private channel, call the `authenticatePrivate` method, with channel name and socket id.
This method returns an array whose key is 'auth' and whose value is the signed authentication string. It's up to you
to encode this as a JSON string (typically done in a controller in a MVC architecture) to return it to the client:

```php
$result = $service->authenticatePrivate('private-channel', '1234.1234');

var_dump($result); // prints array('auth' => 'authentication-string')
```

### Authenticate presence channels

To authenticate a user against a presence channel, call the `authenticatePresence` method, with channel name, socket id
and user data. This method returns an array that contains values for `auth` and `channel_data` keys. It's up to you to
encode this as a JSON string (typically done in a controller in a MVC architecture) to return it to the client:

```php
$result = $service->authenticatePresence('presence-channel', '1234.1234', array('firstName' => 'Michael'));

var_dump($result); // prints array('auth' => 'authentication-string', 'channel_data' => '{"firstName":"Michael"}')
```

## Advanced use

### Error handling

When using the Pusher service, all exceptions that may occurred are handled, so that you can easily filter Pusher
errors. All Pusher exceptions implement the `Pusher\Exception\ExceptionInterface`:

```php
use Pusher\Exception\ExceptionInterface as PusherExceptionInterface;

try {
    $result = $service->getPresenceUsers('presence-foobar');
} catch (PusherExceptionInterface $e) {
    // Handle exception
}
```

Service instantiate concrete exceptions based on the error status code:

* `Pusher\Service\Exception\UnauthorizedException`: thrown when Pusher REST API returns a 401 error (not authorized).
* `Pusher\Service\Exception\ForbiddenException`: thrown when Pusher REST API returns a 403 error (when the application may be disabled, or when you have reached your messages quota).
* `Pusher\Service\UnknownResourceException`: thrown when Pusher REST API returns a 404 error (may occur when you ask information about an unknown channel, for instance)
* `Pusher\Service\RuntimeException`: thrown for any other errors.

In all cases, you can find more information about the error by calling ```php $exception->getMessage();```.

Usage example:

```php
use Pusher\Exception\ExceptionInterface as PusherExceptionInterface;
use Pusher\Service\Exception\ForbiddenException;

try {
    $result = $service->getPresenceUsers('presence-foobar');
} catch(ForbiddenException $e) {
    // Oops, we may have reached our messages quota... Let's do something!
} catch (PusherExceptionInterface $e) {
    // Any other Pusher exception...
} catch (\Exception $e) {
    // Any other non-Pusher exception...
}
```

### Debug applications

In previous Pusher PHP client, you could attach a logger directly to the client through a `set_logger` method. While
simple, this was a bad way of doing it as it was hard-coded into the client (and your logger had to have a `log` method,
so your own logger may not have it). Furthermore, the places where logging occurred were hardcoded also.

Instead, new Pusher PHP client takes advantage of an event manager to do this. For instance, let's say we want to log
every URL BEFORE the request is sent. Let's first create a subscriber. A subscriber implements the interface
`Symfony\Component\EventDispatcher\EventSubscriberInterface`. In the `getSubscribedEvents` method, we attach a listener
for the event `request.before_send` (you can find a complete list of available hooks [here](http://guzzlephp.org/guide/http/creating_plugins.html#event-hooks)):

```php
<?php

namespace Application\Logger;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PusherLogger implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            'request.before_send' => array('log', -255)
        );
    }

    /**
     * Log something
     *
     * @param  Event $event
     * @return void
     */
    public function log(Event $event)
    {
        $request = $event['request'];
        $url     = $request->getUrl();

        // Log the URL...
    }
}
```

Next, we need to attach the subscriber to the client:

```php
use Pusher\Client\Credentials;
use Pusher\Client\PusherClient;
use Pusher\Service\PusherService;

$credentials = new Credentials('application-id', 'key', 'secret');
$client      = new PusherClient($credentials);

$client->addSubscriber(new PusherLogger());

$service = new PusherService($client);
```

And voilà, now all the URL will be logged.

### Directly use the client

While the Pusher service is convenient, you may want to directly use the Pusher client instead, so that you can have
better control of how requests are sent. You can do this:

```php
use Pusher\Client\Credentials;
use Pusher\Client\PusherClient;

$credentials = new Credentials('application-id', 'key', 'secret');
$client      = new PusherClient($credentials);

// Let's do a trigger
$parameters = array(
    'event'     => 'my-event',
    'channel'   => 'my-channel',
    'data'      => array('key' => 'value'),
    'socket_id' => '1234.1234'
);

$command = $client->getCommand('Trigger', $parameters)
                  ->execute();
```

> When using the client directly, the exceptions thrown when errors occurred are Guzzle exceptions, not Pusher exceptions.
Therefore it is harder to filter Pusher only exceptions. If you want this feature, please use the service instead, or
write your own wrapper around the Pusher client.
