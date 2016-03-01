# Changelog

## 2.3.0 (2015-02-16)

[ADDED] A new `cluster` option for the Pusher constructor.

## 2.2.2 (2015-05-15)

[FIXED] Fixed a PHP 5.2 incompatibility caused by referencing a private method in array_walk.

## 2.2.1 (2015-05-13)

[FIXED] Channel name and socket_id values are now validated.
[BROKE] Inadvertently broke PHP 5.2 compatibility by referencing a private method in array_walk.

## 2.2.0 (2015-01-20)

[CHANGED] `new Pusher($app_key, $app_secret, $app_id, $options)` - The `$options` parameter
has been added as the forth parameter to the constructor and other additional
parameters are now deprecated.

## 2.1.3 (2012-12-22)

[NEW] `$pusher->trigger` can now take an `array` of channel names as a first parameter to allow the same event to be published on multiple channels.
[NEW] `$pusher->get` generic function can be used to make `GET` calls to the REST API
[NEW] `$pusher->set_logger` to allow internal logging to be exposed and logged in your own logs.

## 2.1.2 (2012-11-18)

[CHANGED] Debug response from `$pusher->trigger` call is now an associative array in the form `array( 'body' => '{String} body text of response', 'status' => '{Number} http status of the response' )`

## 2.1.1 (2012-10-07)

[CHANGED] Added optional $options parameter to get_channel_info. get_channel_info($channel, $options = array() )

## 2.1.0 (2012-09-28)

[CHANGED] Renamed get_channel_stats to get_channel_info
[CHANGED] get_channels now takes and $options parameter. get_channels( $options = array() )
[REMOVED] get_presence_channels

## 2.0.1 (2012-09-18)

[FIXED] Overwritten socket_id parameter in trigger: https://github.com/pusher/pusher-php-server/pull/3

## 2.0.0 (2012-08-30)

[NEW] Versioning introduced at 2.0.0

[NEW] Added composer.json for submission to http://packagist.org/

[CHANGED] `get_channels()` now returns an object which has a `channels` property. This must be accessed to get the Array of channels in an application.

[CHANGED] `get_presence_channels()` now returns an object which has a `channels` property. This must be accessed to get the Array of channels in an application.
