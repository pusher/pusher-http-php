<?php namespace PusherREST;

if (!extension_loaded('json'))
{
    throw new Exception('There is missing dependant extensions - please ensure that the JSON module is installed');
}

/**
* Generates a JSON that can be loaded by socket libraries.
*
* @param $socket_id string Id of the socket to authorize
* @param $channl string Name of the channel to authorize
* @param $custom_data null|string Additional data to authorize
* @return string json data
**/
public function presenceChannel($socket_id, $channel, $user_id, $user_info = null)
{
    $channel_data = array('user_id' => $user_id);
    if (!is_null($user_info)) {
        $channel_data['user_info'] = $user_info;
    }
    return delegate($socket_id, $channel, $channel_data);
}

public function privateChannel($socket_id, $channel)
{
    return delegate($socket_id, $channel);
}

public function checkCompatiblity()
{

}

public function delegate($socket_id, $channel, $channel_data = null)
    {
        if (!is_null($channel_data)) {
            $channel_data = json_encode($channel_data);
        }

        $signature = $this->sign($socket_id, $channel, $channel_data);

        $json = array('auth' => $this->key . ':' . $signature);

        if (!is_null($channel_data)) {
            $json['channel_data'] = $channel_data;
        }
        return json_encode( $json );
    }
