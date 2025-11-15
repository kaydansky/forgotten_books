<?php

namespace ForgottenBooks\Helpers;

/**
 * Description of FormatDate
 *
 * @author AlexK
 */
class Format
{
    static function date($date)
    {
        $d = date_create($date);
        return date_format($d, 'F j, Y');
    }

    static function dateTime($dateTime, $shorten = false)
    {
        $format = $shorten ? 'n/j/y h:i A' : 'F j, Y h:i A';
        $d = date_create($dateTime);
        return date_format($d, $format);
    }
    
    static function time($time)
    {
        return substr($time, 0, -3);
    }

}
