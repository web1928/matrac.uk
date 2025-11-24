<?php

namespace App\Middleware;

use Core\Middleware;

/**
 * Role Middleware
 * Checks if user has required role
 */
class RoleMiddleware extends Middleware
{
    protected $allowedRoles;

    /**
     * Constructor
     * 
     * @param array|string $roles Allowed role(s)
     */
    public function __construct($roles)
    {
        $this->allowedRoles = is_array($roles) ? $roles : [$roles];
    }

    public function handle()
    {
        // Must be logged in first
        if (!isLoggedIn()) {
            header('Location: ' . url('/login'));
            exit;
        }

        $user = getCurrentUser();
        $userRole = $user['role'] ?? null;

        // Check if user has required role
        if (!in_array($userRole, $this->allowedRoles)) {
            // User doesn't have permission
            http_response_code(403);
            echo "<!DOCTYPE html>
<html>
<head>
    <title>403 - Forbidden</title>
    <style>
        body { font-family: sans-serif; text-align: center; padding: 100px; }
        h1 { font-size: 50px; margin: 0; color: #dc3545; }
        p { font-size: 20px; color: #666; }
        a { color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <h1>403</h1>
    <p>You don't have permission to access this page.</p>
    <p><a href='" . url('/dashboard') . "'>Return to Dashboard</a></p>
</body>
</html>";
            exit;
        }

        return true;
    }
}
