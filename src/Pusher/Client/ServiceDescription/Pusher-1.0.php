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
        'Trigger' => array(
            'httpMethod' => 'POST',
            'uri'        => 'apps/{app_id}/events',
            'summary'    => 'Trigger a new event to one or multiple channel',
            'parameters' => array(
                'app_id' => array(
                    'description' => 'Application identifier',
                    'location'    => 'uri',
                    'type'        => 'integer',
                    'required'    => true
                ),
                'event' => array(
                    'description' => 'Event name to trigger',
                    'sentAs'      => 'name',
                    'location'    => 'json',
                    'type'        => 'string',
                    'required'    => true
                ),
                'data' => array(
                    'description' => 'Data to sent with the event',
                    'location'    => 'json',
                    'type'        => 'string',
                    'required'    => true
                ),
                'channels' => array(
                    'description' => 'Array of channel names',
                    'location'    => 'postField',
                    'type'        => 'array',
                    'required'    => false,
                    'maxItems'    => 100,
                    'items'       => array(
                        'type' => 'string'
                    )
                ),
                'channel' => array(
                    'description' => 'Single channel name',
                    'location'    => 'json',
                    'type'        => 'string',
                    'required'    => true,
                ),
            )
        ),
    )
);
