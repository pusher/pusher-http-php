<?php namespace Pusher;

/**
 * @package     Pusher
 * @copyright   2011,   Squeeks
 * @licence     http://www.opensource.org/licenses/mit-license.php  MIT
 */


class Helper
{
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
