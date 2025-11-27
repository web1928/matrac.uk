<?php

/**
 * Load environment variables from .env file
 */

function loadEnv($path)
{
    if (!file_exists($path)) {

        throw new Exception(".env file not found at $path");
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parse KEY=VALUE
        if (strpos($line, '=') === false) {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        // Remove quotes
        $value = trim($value, '"\'');

        // Set environment variable if not already set
        if (!getenv($name)) {
            putenv("$name=$value");
        }
    }
}

// Load .env file
loadEnv(dirname(__DIR__) . '/.env');
