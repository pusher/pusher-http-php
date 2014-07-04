Puser REST library for PHP5
===========================

This is the official client library for our REST API.
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
$pusher = new pusher\Pusher(getenv('PUSHER_URL'));
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


