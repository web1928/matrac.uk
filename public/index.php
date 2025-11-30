<?php
/**
 * MatraC - Application Entry Point
 * All requests flow through here
 */
// Load bootstrsap file
require_once '../bootstrap.php';

// Create router
use Matrac\Framework\Router;

$router = new Router();

// Load routes
if (file_exists(ROOT_PATH . '/config/routes.php')) {
    require_once ROOT_PATH . '/config/routes.php';
} else {
    throw new Exception('Unable to access the route list!');
};

// Dispatch request
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
