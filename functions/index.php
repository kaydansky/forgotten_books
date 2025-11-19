<?php

function handler($event, $context) {
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
    
    $_SERVER['REQUEST_METHOD'] = $event['httpMethod'] ?? 'GET';
    $_SERVER['REQUEST_URI'] = $event['path'] ?? '/';
    $_SERVER['QUERY_STRING'] = $event['queryStringParameters'] ? http_build_query($event['queryStringParameters']) : '';
    $_SERVER['SCRIPT_FILENAME'] = $target . '/index.php';
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    $_SERVER['PHP_SELF'] = '/index.php';
    
    ob_start();
    
    try {
        require $target . '/index.php';
        $body = ob_get_contents();
    } catch (Exception $e) {
        $body = 'Error: ' . $e->getMessage();
    } finally {
        ob_end_clean();
    }
    
    return [
        'statusCode' => 200,
        'headers' => [
            'Content-Type' => 'text/html; charset=utf-8'
        ],
        'body' => $body
    ];
}

return handler($event ?? [], $context ?? []);