<?php

declare(strict_types=1);

namespace App\Controllers;

use Matrac\Framework\Controller;
use App\Models\Auth;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin(): void
    {
        // If already logged in, redirect to dashboard
        if (isLoggedIn()) {
            $this->redirect('/dashboard');
        }
        $this->view('auth.login');
    }

    /**
     * Process login
     */
    public function login()
    {
        // Verify CSRF token
        if (!validateCsrfToken($this->request->input('csrf_token'))) {
            $this->flash('error', 'Invalid request. Please try again.');
            return $this->redirect('/login');
        }

        $username = (string) $this->request->input('username');
        $password = (string) $this->request->input('password');

        // Validate inputs
        if (empty($username) || empty($password)) {
            $this->flash('error', 'Username and password are required');
            return $this->redirect('/login');
        }

        $user = Auth::getUserRecord($username);

        // Verify user exists and has valid password hash
        if ($user && isset($user['password_hash']) && !empty($user['password_hash'])) {

            if (password_verify($password, $user['password_hash'])) {

                // Validate required fields
                if (empty($user['user_id']) || empty($user['username']) || empty($user['role'])) {
                    error_log("Invalid user data for username: $username");
                    $this->flash('error', 'Account error. Please contact administrator.');
                    return $this->redirect('/login');
                }

                // Update lastlogin
                Auth::updateUserLastLogin($user['user_id']);

                // Set session variables
                $_SESSION['user'] = [
                    'id' => $user['user_id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'role' => $user['role']
                ];

                // Regenerate session ID for security
                session_regenerate_id(true);

                // Check for intended URL
                $intendedUrl = $_SESSION['intended_url'] ?? '/dashboard';
                unset($_SESSION['intended_url']);

                // Redirect
                return $this->redirect($intendedUrl);
            }
        }

        // Invalid credentials (generic message for security)
        $this->flash('error', 'Invalid username or password');
        return $this->redirect('/login');
    }

    /**
     * Logout user
     * Destroys session and regenerates ID
     */
    function logout()
    {

        // Clear session data
        $_SESSION = [];

        // Destroy session cookie
        if (isset($_COOKIE[session_name()])) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // Destroy session
        session_destroy();

        // Redirect to login using url() helper
        return $this->redirect('/login');
    }
}
