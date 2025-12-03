<?php

namespace Matrac\Framework;

/**
 * Request Class
 * Handles HTTP request data
 */
class Request
{
    /**
     * Get input value
     */
    public function input($key, $default = null)
    {
        // Check POST first
        if (isset($_POST[$key])) {
            return $this->clean($_POST[$key]);
        }

        // Then GET
        if (isset($_GET[$key])) {
            return $this->clean($_GET[$key]);
        }

        return $default;
    }

    /**
     * Get all input
     */
    public function all(): array
    {
        return array_merge($_GET, $_POST);
    }

    /**
     * Check if input exists
     */
    public function has($key): bool
    {
        return isset($_POST[$key]) || isset($_GET[$key]);
    }

    /**
     * Get request method
     */
    public function method(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Check if request is POST
     */
    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    /**
     * Check if request is GET
     */
    public function isGet(): bool
    {
        return $this->method() === 'GET';
    }

    /**
     * Check if request is AJAX
     */
    public function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Clean input value
     */
    protected function clean($value): array|string
    {
        if (is_array($value)) {
            return array_map([$this, 'clean'], $value);
        }

        return trim($value);
    }
}
