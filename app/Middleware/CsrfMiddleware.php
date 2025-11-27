<?php

namespace App\Middleware;

use Matrac\Framework\Middleware;

/**
 * CSRF Middleware
 * Validates CSRF token on POST/PUT/DELETE requests
 */
class CsrfMiddleware extends Middleware
{
    public function handle()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        // Only validate on state-changing methods
        if (in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

            if (!validateCsrfToken($token)) {
                http_response_code(403);
                echo "<!DOCTYPE html>
<html>
<head>
    <title>403 - CSRF Token Invalid</title>
    <style>
        body { font-family: sans-serif; text-align: center; padding: 100px; }
        h1 { font-size: 50px; margin: 0; color: #dc3545; }
        p { font-size: 20px; color: #666; }
    </style>
</head>
<body>
    <h1>403</h1>
    <p>CSRF token validation failed.</p>
    <p>Please refresh the page and try again.</p>
</body>
</html>";
                exit;
            }
        }

        return true;
    }
}
