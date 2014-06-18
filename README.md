Puser REST library for PHP5
===========================

This is the official client library for our REST API.
See http://pusher.com/docs/rest for the documentation of the API.

Installation
------------

Use the [composer](http://getcomposer.org) to install this bundle.

```
$ composer require pusher/pusher-rest-php
```

Installation
------------

This library is part of the composer.org
`composer get pusher-rest-php`

Usage
-----

```php
$client = new pusher\RESTClient([
  'api_key' => 'key',
  'api_secret' => 'secret'
  'host' => 'api.pusher.com',
  'use_ssl' => false,
  'timeout' => 10,
  'proxy' => '10.13.37.4:80'
]);
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

use guzzle ? https://github.com/guzzle/guzzle/tree/master/src

Framework integration
---------------------

http://www.sitepoint.com/best-php-frameworks-2014/

Laravel, Phalcon, Symphony2


