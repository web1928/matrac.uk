<?php

namespace App\Middleware;

use Core\Middleware;

/**
 * Authentication Middleware
 * Ensures user is logged in
 */
class AuthMiddleware extends Middleware
{
    public function handle()
    {
        // Check if user is logged in
        if (!isLoggedIn()) {
            // Store intended URL for redirect after login
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];

            // Redirect to login
            header('Location: ' . url('/login'));
            exit;
        }

        return true;
    }
}
