<?php

use ForgottenBooks\DI\DiResolver;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config/config.php';

$route = (new DiResolver)->resolve('ForgottenBooks\Router\Router');
$route->request();
$route->response($route->output);
