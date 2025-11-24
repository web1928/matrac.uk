<?php

/**
 * MatraC - Application Entry Point
 * All requests flow through here
 */

// Define root path
define('ROOT_PATH', dirname(__DIR__));

// Load environment
require_once ROOT_PATH . '/config/env.php';

// Load helpers
require_once ROOT_PATH . '/app/helpers.php';

// Load existing auth functions (keep for now)
require_once ROOT_PATH . '/includes/auth.php';

// Load database config
require_once ROOT_PATH . '/config/database.php';

// PSR-4 Autoloader
spl_autoload_register(function ($class) {
    $file = ROOT_PATH . '/' . str_replace('\\', '/', $class) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Initialize session
initSecureSession();

// Create router
$router = new Core\Router();

// Load routes
require_once ROOT_PATH . '/routes/web.php';

// Dispatch request
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
