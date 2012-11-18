# Changelog

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