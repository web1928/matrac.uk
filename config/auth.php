<?php
declare (strict_types=1);
/**
 * Session and Authentication Utilities
 * 
 * Secure session management following OWASP guidelines
 * Will be expanded in Phase 2 with full authentication
 */

/**
 * Initialize secure session
 * 
 * Implements security best practices:
 * - HttpOnly cookies (prevent XSS access)
 * - Secure flag (HTTPS only - disabled for local dev)
 * - SameSite=Strict (prevent CSRF)
 * - Session regeneration on login
 */
function initSecureSession()
{
    // Prevent session fixation attacks
    if (session_status() === PHP_SESSION_NONE) {

        // Configure session cookie parameters
        $cookieParams = [
            'lifetime' => 0,           // Session cookie (expires on browser close)
            'path' => '/',             // Available site-wide
            'domain' => '',            // Current domain
            'secure' => false,         // Set to true when using HTTPS
            'httponly' => true,        // Prevent JavaScript access
            'samesite' => 'Strict'     // CSRF protection
        ];

        session_set_cookie_params($cookieParams);

        // Use strict session ID generation
        ini_set('session.use_strict_mode', 1);
        ini_set('session.use_only_cookies', 1);

        session_start();

        // Regenerate session ID periodically (every 30 minutes)
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
}

/**
 * Check if user is authenticated
 * 
 * @return bool True if user is logged in
 */
function isLoggedIn():bool
{
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}


/**
 * Get current user data
 * 
 * @return array User data (id, username, role, first_name, last_name)
 */
function getCurrentUser():mixed
{
    if (!isLoggedIn()) {
        return null;
    }

    return [
        'user_id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'role' => $_SESSION['user_role'] ?? null,
        'first_name' => $_SESSION['first_name'] ?? null,
        'last_name' => $_SESSION['last_name'] ?? null,
    ];
}

/**
 * Generate CSRF token
 * 
 * @return string CSRF token
 */
function generateCsrfToken():string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 * 
 * @param string $token Token to validate
 * @return bool True if valid
 */
function validateCsrfToken(string $token)
{
    // Return false if token is null or empty
    if (empty($token) || !isset($_SESSION['csrf_token'])) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}


/**
 * Check if user has specific role
 * 
 * @param string|array $allowedRoles Role(s) to check against
 * @return bool True if user has role
 */
function hasRole($allowedRoles)
{
    if (!isLoggedIn()) {
        return false;
    }

    $userRole = $_SESSION['user_role'] ?? null;

    if (is_array($allowedRoles)) {
        return in_array($userRole, $allowedRoles);
    }

    return $userRole === $allowedRoles;
}
