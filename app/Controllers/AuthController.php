<?php

namespace App\Controllers;

use Core\Controller;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        // If already logged in, redirect to dashboard
        if (isLoggedIn()) {
            return $this->redirect('/dashboard');
        }

        return $this->view('auth.login');
    }

    /**
     * Process login
     */
    public function login()
    {

        $username = $this->request->input('username');
        $password = $this->request->input('password');

        // Validate inputs
        if (empty($username) || empty($password)) {
            $this->flash('error', 'Username and password are required');
            return $this->redirect('/login');
        }

        // TEMPORARY: Hardcoded test users (Phase 2)
        // Phase 3 will use database authentication
        $testUsers = [
            'admin' => [
                'password' => 'admin123',
                'role' => 'admin',
                'first_name' => 'Danny',
                'last_name' => 'Mason',
                'user_id' => 1
            ],
            'receptor' => [
                'password' => 'test123',
                'role' => 'goods_receptor',
                'first_name' => 'John',
                'last_name' => 'Receptor',
                'user_id' => 2
            ],
            'issuer' => [
                'password' => 'test123',
                'role' => 'goods_issuer',
                'first_name' => 'Jane',
                'last_name' => 'Issuer',
                'user_id' => 3
            ],
            'mixer' => [
                'password' => 'test123',
                'role' => 'mixer',
                'first_name' => 'Mike',
                'last_name' => 'Mixer',
                'user_id' => 4
            ],
        ];

        // Check credentials
        if (isset($testUsers[$username]) && $testUsers[$username]['password'] === $password) {
            // Set session variables
            $_SESSION['user_id'] = $testUsers[$username]['user_id'];
            $_SESSION['username'] = $username;
            $_SESSION['user_role'] = $testUsers[$username]['role'];
            $_SESSION['first_name'] = $testUsers[$username]['first_name'];
            $_SESSION['last_name'] = $testUsers[$username]['last_name'];

            // Regenerate session ID for security
            session_regenerate_id(true);

            // Check for intended URL (where they were trying to go)
            $intendedUrl = $_SESSION['intended_url'] ?? '/dashboard';
            unset($_SESSION['intended_url']);

            // Redirect
            return $this->redirect($intendedUrl);
        }

        // Invalid credentials
        $this->flash('error', 'Invalid username or password');
        return $this->redirect('/login');
    }

    /**
     * Logout user
     * Destroys session and regenerates ID
     */
    function logout()
    {
        initSecureSession();

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
        header('Location: ' . url('/login'));
        exit;
    }
}
