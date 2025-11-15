<?php
/**
 * @author: AlexK
 * Date: 15-Jan-19
 * Time: 10:31 PM
 */

namespace ForgottenBooks\Helpers;


class Sanitizer
{
    public function __construct(){}

    static function sanitize($var = null, $length = 1000)
    {
        if (! empty($var)) {
            return substr(trim($var), 0, $length);
        }
    }
}