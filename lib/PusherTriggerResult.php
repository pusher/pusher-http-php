<?php

/**
 * Represents the result of a call to {@link Pusher::trigger}.
 */
class PusherTriggerResult {

	/**
	* A lookup of the event ID of a trigger on a given channel.
	* e.g. $ch1EventId = $triggerResult['ch1'];
	*
	* @return array
	*/
	public $eventIds = array();

	/**
	 * @param array $decodedJson The decoded JSON triggerresponse from the Pusher HTTP API.
	 */
	public function __construct($decodedJson) {
		foreach($decodedJson['event_ids'] as $channel => $eventId ) {
			$this->eventIds[ $channel ] = $eventId;
		}
	}
}
