<?php

require __DIR__ . '/../classes/Repository.php';
require __DIR__ . '/../classes/Controller.php';
require __DIR__ . '/../classes/View.php';

$routes = require __DIR__ . '/../routes.php';
$config = require __DIR__ . '/../config.php';

$path = $_SERVER['REDIRECT_URL'] ?? '/';
if (isset($routes[$path])) {
    $class = $routes[$path][0];
    $method = $routes[$path][1];
    $controller = new $class(new Repository($config['host'], $config['port'], $config['db'], $config['user'], $config['password']));
    if (method_exists($controller, $method)) {
        $request = $_REQUEST ?? [];
        call_user_func_array([$controller, $method], $request);
    }
}
