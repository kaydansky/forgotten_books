<?php
/**
 * @author: AlexK
 * Date: 16-Jan-19
 * Time: 1:50 PM
 */

namespace ForgottenBooks\Helpers;


class ResponseCode
{
    public static function code404()
    {
        http_response_code(404);
        die();
    }
}