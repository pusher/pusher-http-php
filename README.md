Puser REST library for PHP5
===========================

This is the official client library for our REST API.
See http://pusher.com/docs/rest for the documentation of the API.

Installation
------------

Use the [composer](http://getcomposer.org) to install this bundle.

```
$ composer require pusher/PusherREST
```

Installation
------------

This library is part of the composer.org
`composer get PusherREST

Usage
-----

```php
$config = new PusherREST\Config(array(
    'api_url' => 'http://key:secret@api.pusher.com/apps/3455'
));
$client = new PusherREST\Client($config);
$client->trigger('channel_name', 'event_name', 'data');
```

Configuration
-------------

Compatibility
-------------

This library follows [Semantic Versioning](http://semver.org).

TODO
====

secure the auth end-point

Framework integration
---------------------

http://www.sitepoint.com/best-php-frameworks-2014/

Laravel, Phalcon, Symphony2


