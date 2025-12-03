<?php

/**
 * Global Helper Functions
 */


/**
 * Generate URL
 */
function url($path = '')
{
    static $baseUrl = null;

    if ($baseUrl === null) {
        $baseUrl = rtrim(getenv('APP_URL') ?: 'http://localhost', '/');
    }

    return $baseUrl . '/' . ltrim($path, '/');
}

/**
 * Generate asset URL
 */
function asset($path)
{
    return url('assets/' . ltrim($path, '/'));
}

/**
 * HTML escape
 */
function h($string)
{
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Dump and die (debugging)
 */
function dd(...$vars)
{
    echo '<pre>';
    foreach ($vars as $var) {
        var_dump($var);
    }
    echo '</pre>';
    die();
}

/**
 * Get flash message and clear it
 */
function flash($key, $value = null)
{
    if ($value === null) {
        // Get flash message
        $message = $_SESSION['flash'][$key] ?? null;
        if (isset($_SESSION['flash'][$key])) {
            unset($_SESSION['flash'][$key]);
        }
        return $message;
    }

    // Set flash message
    $_SESSION['flash'][$key] = $value;
}
