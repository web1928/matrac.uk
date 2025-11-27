<?php

// Define root path
define('ROOT_PATH', __DIR__);

// Load autoloader
require_once ROOT_PATH . '/vendor/autoload.php';

// Set error handling
error_reporting(E_ALL);
// error_reporting(0);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

use Matrac\Framework\ErrorHandler;

// In your bootstrap/init file
$errorHandler = new ErrorHandler();
$errorHandler->register();


// Load helpers
require_once ROOT_PATH . '/App/helpers.php';

// Load environment
require_once ROOT_PATH . '/config/env.php';

// Load existing auth functions (keep for now)
require_once ROOT_PATH . '/config/auth.php';

// Load database config
require_once ROOT_PATH . '/config/database.php';

date_default_timezone_set(getenv('APP_TIMEZONE') ?: 'Europe/London');

// Initialize session
initSecureSession();
