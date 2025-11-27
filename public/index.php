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
require_once ROOT_PATH . '/config/routes.php';

// Dispatch request
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
