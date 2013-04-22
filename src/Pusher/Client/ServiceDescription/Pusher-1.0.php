<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

return array(
    'name'        => 'Pusher',
    'apiVersion'  => '1.0',
    'baseUrl'     => 'https://api.pusherapp.com',
    'description' => 'Pusher is a service that brings real-timeness to your web applications',
    'operations'  => array(
        'GetChannelInfo' => array(
            'httpMethod'       => 'GET',
            'uri'              => 'apps/{app_id}/channels/{channel}',
            'summary'          => 'Get information about a given channel',
            'responseClass'    => 'GetChannelInfoResult',
            'responseType'     => 'model',
            'documentationUrl' => 'http://pusher.com/docs/server_api_guide/interact_rest_api#method-get-channels',
            'parameters'       => array(
                'app_id'  => array(
                    'description' => 'Application identifier',
                    'location'    => 'uri',
                    'type'        => 'integer',
                    'required'    => true
                ),
                'channel' => array(
                    'description' => 'Single channel name',
                    'location'    => 'uri',
                    'type'        => 'string',
                    'required'    => true,
                    'pattern'     => '/^[a-zA-Z0-9.,;_=\-@]+$/'
                ),
                'info' => array(
                    'description' => 'List of info to return (currently, can be only user_count or subscription_count)',
                    'location'    => 'query',
                    'type'        => 'array',
                    'required'    => false,
                    'filters'     => array(
                        array(
                            'method' => 'implode',
                            'args'   => array(',', '@value')
                        )
                    ),
                    'items' => array(
                        'type' => 'string',
                        'enum' => array('user_count', 'subscription_count')
                    )
                )
            )
        ),
        'GetChannelsInfo' => array(
            'httpMethod'       => 'GET',
            'uri'              => 'apps/{app_id}/channels',
            'summary'          => 'Get information about multiple channels',
            'responseClass'    => 'GetChannelsInfoResult',
            'responseType'     => 'model',
            'documentationUrl' => 'http://pusher.com/docs/server_api_guide/interact_rest_api#method-get-channel',
            'parameters'       => array(
                'app_id' => array(
                    'description' => 'Application identifier',
                    'location'    => 'uri',
                    'type'        => 'integer',
                    'required'    => true
                ),
                'prefix' => array(
                    'description' => 'Filter channels by a given prefix (eg. "presence-")',
                    'sentAs'      => 'filter_by_prefix',
                    'location'    => 'query',
                    'type'        => 'string',
                    'required'    => false
                ),
                'info' => array(
                    'description' => 'List of info to return (currently, can be only user_count)',
                    'location'    => 'query',
                    'type'        => 'array',
                    'required'    => false,
                    'filters'     => array(
                        array(
                            'method' => 'implode',
                            'args'   => array(',', '@value')
                        )
                    ),
                    'items' => array(
                        'type' => 'string',
                        'enum' => array('user_count')
                    )
                )
            )
        ),
        'GetPresenceUsers' => array(
            'httpMethod'       => 'GET',
            'uri'              => 'apps/{app_id}/events/{channel}/users',
            'summary'          => 'Get a list of user identifiers that are currently subscribed to a given presence channel',
            'responseClass'    => 'GetPresenceUsersResult',
            'responseType'     => 'model',
            'documentationUrl' => 'http://pusher.com/docs/server_api_guide/interact_rest_api#method-get-users',
            'parameters'       => array(
                'app_id'  => array(
                    'description' => 'Application identifier',
                    'location'    => 'uri',
                    'type'        => 'integer',
                    'required'    => true
                ),
                'channel' => array(
                    'description' => 'Presence channel name (name must begin by presence-)',
                    'location'    => 'uri',
                    'type'        => 'string',
                    'required'    => true,
                    'pattern'     => '/^presence-[a-zA-Z0-9.,;_=\-@]+$/'
                )
            )
        ),
        'Trigger' => array(
            'httpMethod'       => 'POST',
            'uri'              => 'apps/{app_id}/events',
            'summary'          => 'Trigger a new event to one or multiple channel',
            'responseClass'    => 'EmptyResult',
            'responseType'     => 'model',
            'documentationUrl' => 'http://pusher.com/docs/server_api_guide/interact_rest_api#method-post-event',
            'parameters'       => array(
                'app_id'    => array(
                    'description' => 'Application identifier',
                    'location'    => 'uri',
                    'type'        => 'integer',
                    'required'    => true
                ),
                'event'     => array(
                    'description' => 'Event name to trigger',
                    'sentAs'      => 'name',
                    'location'    => 'json',
                    'type'        => 'string',
                    'required'    => true
                ),
                'data'      => array(
                    'description' => 'Data to sent with the event',
                    'location'    => 'json',
                    'type'        => 'array',
                    'filters'     => array('json_encode'),
                    'required'    => true
                ),
                'channels'  => array(
                    'description' => 'Array of channel names',
                    'location'    => 'postField',
                    'type'        => 'array',
                    'required'    => false,
                    'maxItems'    => 100,
                    'items'       => array(
                        'type'    => 'string',
                        'pattern' => '/^[a-zA-Z0-9.,;_=\-@]+$/'
                    )
                ),
                'channel'   => array(
                    'description' => 'Single channel name',
                    'location'    => 'json',
                    'type'        => 'string',
                    'required'    => false,
                    'pattern'     => '/^[a-zA-Z0-9.,;_=\-@]+$/'
                ),
                'socket_id' => array(
                    'description' => 'Socket identifier to be excluded from receiving the event',
                    'location'    => 'json',
                    'type'        => 'string',
                    'required'    => false
                )
            )
        ),
    ),
    'models' => array(
        'GetChannelInfoResult' => array(
            'type'       => 'object',
            'properties' => array(
                'occupied' => array(
                    'description' => 'Indicate if the channel is currently occupied',
                    'location'    => 'json',
                    'type'        => 'boolean'
                ),
                'user_count' => array(
                    'description' => 'Number of distinct users currently subscribed to this channel (a single user may be subscribed many times, but will only count as one)',
                    'location'    => 'json',
                    'type'        => 'string'
                ),
                'subscription_count' => array(
                    'description' => 'Number of connections currently subscribed to this channel',
                    'location'    => 'json',
                    'type'        => 'string'
                )
            )
        ),
        'GetChannelsInfoResult' => array(
            'type'       => 'object',
            'properties' => array(
                'channels' => array(
                    'location'   => 'json',
                    'type'       => 'array',
                    'items'      => array(
                        'type'       => 'object',
                        'properties' => array(
                            'user_count' => array(
                                'description' => 'Number of distinct users currently subscribed to this channel (a single user may be subscribed many times, but will only count as one)',
                                'location'    => 'json',
                                'type'        => 'string'
                            )
                        )
                    )
                )
            )
        ),
        'GetPresenceUsersResult' => array(
            'type'       => 'object',
            'properties' => array(
                'users' => array(
                    'location' => 'json',
                    'type'     => 'array',
                    'items'    => array(
                        'type'       => 'object',
                        'properties' => array(
                            'id' => array(
                                'description' => 'Identifier of the user in the channel',
                                'location'    => 'json',
                                'type'        => 'integer'
                            )
                        )
                    )
                )
            )
        ),
        'EmptyResult' => array(
            'type' => 'object'
        ),
    )
);
