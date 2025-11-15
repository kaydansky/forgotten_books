<?php
/**
 * @author: AlexK
 * Date: 30-Jan-19
 * Time: 9:54 PM
 */

namespace ForgottenBooks\Helpers;


class Encrypt
{
    private static function cryptoRand($min, $max)
    {
        $range = $max - $min;
        if ($range < 1) {
            return $min;
        }

        $log = ceil(log($range, 2));
        $bytes = (int) ($log / 8) + 1;
        $bits = (int) $log + 1;
        $filter = (int) (1 << $bits) - 1;

        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter;
        } while ($rnd > $range);
        return $min + $rnd;
    }

    public static function randomString($length)
    {
        $token = '';
        $codeAlphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $codeAlphabet.= 'abcdefghijklmnopqrstuvwxyz';
        $codeAlphabet.= '0123456789';
        $max = strlen($codeAlphabet);

        for ($i=0; $i < $length; $i++) {
            $token .= $codeAlphabet[self::cryptoRand(0, $max-1)];
        }

        return $token;
    }
}