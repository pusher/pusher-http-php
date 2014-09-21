<?php namespace Pusher;

/**
 * @package     Pusher
 * @copyright   2011,   Squeeks
 * @licence     http://www.opensource.org/licenses/mit-license.php  MIT
 */


class Helper
{
    /**
     * Build the required HMAC hash for auth string
     *
     * @param string $authKey
     * @param string $authSecret
     * @param string $requestMethod
     * @param string $requestPath
     * @param array  $queryParams
     * @param string $authVersion   [optional]
     * @param string $authTimestamp [optional]
     * @return string
     */
    public static function buildAuthQuery($authKey, $authSecret, $requestMethod, $requestPath,
                                          array $queryParams = array(), $authVersion = '1.0', $authTimestamp = null)
    {
        $queryParams['auth_key'] = $authKey;
        $queryParams['auth_timestamp'] = (is_null($authTimestamp) ? time() : $authTimestamp);
        $queryParams['auth_version'] = $authVersion;

        ksort($queryParams);

        $stringToSign = $requestMethod . "\n" . $requestPath . "\n" . static::arrayToString( '=', '&', $queryParams );

        $auth_signature = hash_hmac('sha256', $stringToSign, $authSecret, false);

        $queryParams['auth_signature'] = $auth_signature;
        ksort($queryParams);

        $authQueryString = static::arrayToString( '=', '&', $queryParams );

        return $authQueryString;
    }

    /**
     * Implode an array with the key and value pair giving
     * a glue, a separator between pairs and the array
     * to implode.
     *
     * @param string $glue
     * @param string $separator
     * @param array $array
     * @return string
     */
    public static function arrayToString( $glue, $separator, $array )
    {
        if( ! is_array($array)) return $array;

        $string = array();

        foreach($array as $key => $val)
        {
            if (is_array($val))
            {
                $val = implode( ',', $val );
            }

            $string[] = $key . $glue . $val;
        }

        return implode($separator, $string);
    }
}
