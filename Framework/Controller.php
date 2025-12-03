<?php

declare(strict_types=1);

namespace Matrac\Framework;

use Exception;
use Matrac\Framework\Request;

/**
 * Base Controller Extending all Controllers
 */
class Controller
{
    protected $request;

    public function __construct()
    {
        $this->request = new Request();
    }

    /**
     * Renders the page view
     *
     * @param string $view
     * @param array $data
     * @return void
     */
    protected function view(string $view, $data = []): void
    {
        // Extract data to variables
        extract($data);

        // Build view path
        $viewPath = ROOT_PATH . '/App/Views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            error_log("View {$view} not found at {$viewPath}");
            throw new Exception("View {$view} not found at {$viewPath}");
        }

        // Start output buffering
        ob_start();

        // Include view file
        require $viewPath;

        // Get buffer content
        $content = ob_get_clean();

        // Output content
        echo $content;
    }

    /* Return JSON response */
    protected function json($data, $statusCode = 200): never
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /* Redirect to URL */
    protected function redirect($path, $statusCode = 302): never
    {
        header('Location: ' . url($path), true, $statusCode);
        exit;
    }

    /* Set flash message */
    protected function flash($key, $message): void
    {
        $_SESSION['flash'][$key] = $message;
    }

    /* Validate CSRF token */
    protected function validateCsrf(): void
    {
        $token = $this->request->input('csrf_token');

        if (!validateCsrfToken($token)) {
            $this->abort(403, 'Invalid CSRF token');
        }
    }

    /* Abort with error */
    protected function abort($code, $message = null): never
    {
        http_response_code($code);
        die($message ?? "Error {$code}");
    }
}
