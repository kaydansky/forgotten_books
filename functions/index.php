<?php
$source = realpath(__DIR__ . '/..'); 
$target = '/tmp/site'; 

if (!is_dir($target)) {
    mkdir($target, 0777, true);

    $copy = function ($from, $to) use (&$copy) {
        foreach (scandir($from) as $item) {
            if ($item === '.' || $item === '..') continue;

            $src = $from . '/' . $item;
            $dst = $to . '/' . $item;

            if (is_dir($src)) {
                mkdir($dst, 0777, true);
                $copy($src, $dst);
            } else {
                copy($src, $dst);
            }
        }
    };

    $copy($source, $target);
}

chdir($target);

$_SERVER['SCRIPT_FILENAME'] = $target . '/index.php';
$_SERVER['SCRIPT_NAME']     = '/index.php';
$_SERVER['PHP_SELF']        = '/index.php';
$_SERVER['REQUEST_URI']     = parse_url($_SERVER['X_ORIGINAL_URI'] ?? '/', PHP_URL_PATH)
                            ?: '/';

foreach ($_ENV as $key => $value) {
    putenv("$key=$value");
}

require 'index.php';