<?php

namespace Matrac\Framework;

/**
 * Router Class
 * Handles URL routing and dispatching to controllers with middleware support
 */
class Router
{
    protected $routes = [];
    protected $middlewares = [];
    protected $groupMiddlewares = [];
    protected $groupPrefix = '';

    /**
     * Register middleware
     */
    public function middleware($name, $class)
    {
        $this->middlewares[$name] = $class;
    }

    /**
     * Add GET route
     */
    public function get($uri, $action)
    {
        $this->addRoute('GET', $uri, $action);
    }

    /**
     * Add POST route
     */
    public function post($uri, $action)
    {
        $this->addRoute('POST', $uri, $action);
    }

    /**
     * Add PUT route
     */
    public function put($uri, $action)
    {
        $this->addRoute('PUT', $uri, $action);
    }

    /**
     * Add DELETE route
     */
    public function delete($uri, $action)
    {
        $this->addRoute('DELETE', $uri, $action);
    }

    /**
     * Add route
     */
    protected function addRoute($method, $uri, $action)
    {
        // Add group prefix if in group
        $uri = $this->groupPrefix . $uri;

        // Normalize URI
        $uri = '/' . trim($uri, '/');
        if ($uri !== '/') {
            $uri = rtrim($uri, '/');
        }

        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'action' => $action,
            'middlewares' => $this->groupMiddlewares
        ];
    }

    /**
     * Route group with middleware or prefix
     */
    public function group($attributes, $callback)
    {
        $previousMiddlewares = $this->groupMiddlewares;
        $previousPrefix = $this->groupPrefix;

        // Handle middleware
        if (isset($attributes['middleware'])) {
            $middlewares = is_array($attributes['middleware'])
                ? $attributes['middleware']
                : [$attributes['middleware']];

            $this->groupMiddlewares = array_merge($this->groupMiddlewares, $middlewares);
        }

        // Handle prefix
        if (isset($attributes['prefix'])) {
            $this->groupPrefix .= '/' . trim($attributes['prefix'], '/');
        }

        $callback($this);

        $this->groupMiddlewares = $previousMiddlewares;
        $this->groupPrefix = $previousPrefix;
    }

    /**
     * Dispatch request
     */
    public function dispatch($requestUri, $requestMethod)
    {
        // Remove query string
        $uri = parse_url($requestUri, PHP_URL_PATH);

        // Normalize URI - handle null safely
        $uri = '/' . trim($uri ?? '', '/');
        if ($uri !== '/') {
            $uri = rtrim($uri, '/');
        }

        // Find matching route
        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod) {
                // Check for exact match
                if ($route['uri'] === $uri) {
                    return $this->handleRoute($route);
                }

                // Check for parameter match
                $pattern = $this->convertToRegex($route['uri']);
                if (preg_match($pattern, $uri, $matches)) {
                    array_shift($matches); // Remove full match
                    return $this->handleRoute($route, $matches);
                }
            }
        }

        // No route found
        $this->abort(404);
    }

    /**
     * Handle route with middleware
     */
    protected function handleRoute($route, $params = [])
    {
        // Run middleware
        if (!empty($route['middlewares'])) {
            foreach ($route['middlewares'] as $middlewareName) {
                if (isset($this->middlewares[$middlewareName])) {
                    $middlewareClass = $this->middlewares[$middlewareName];
                    $middleware = new $middlewareClass();

                    // If middleware returns false, stop execution
                    if ($middleware->handle() === false) {
                        return;
                    }
                }
            }
        }

        // Call controller action
        return $this->callAction($route, $params);
    }

    /**
     * Convert route URI to regex pattern
     */
    protected function convertToRegex($uri)
    {
        // Convert {id} to named capture group
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $uri);
        return '#^' . $pattern . '$#';
    }

    /**
     * Call controller action
     */
    protected function callAction($route, $params = [])
    {
        $action = $route['action'];

        // Check if it's a closure
        if ($action instanceof \Closure) {
            return call_user_func_array($action, $params);
        }

        // Parse Controller@method
        list($controller, $method) = explode('@', $action);

        // Build full controller class name
        $controller = "App\\Controllers\\{$controller}";

        if (!class_exists($controller)) {
            die("Controller {$controller} not found");
        }

        $controllerInstance = new $controller();

        if (!method_exists($controllerInstance, $method)) {
            die("Method {$method} not found in {$controller}");
        }

        // Call controller method with params
        return call_user_func_array([$controllerInstance, $method], $params);
    }

    /**
     * Abort with HTTP status code
     */
    protected function abort($code = 404)
    {
        http_response_code($code);

        $messages = [
            404 => 'Page Not Found',
            403 => 'Forbidden',
            500 => 'Internal Server Error',
        ];

        $message = $messages[$code] ?? 'Error';

        echo "<!DOCTYPE html>
<html>
<head>
    <title>{$code} - {$message}</title>
    <style>
        body { font-family: sans-serif; text-align: center; padding: 100px; }
        h1 { font-size: 50px; margin: 0; }
        p { font-size: 20px; color: #666; }
        a { color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <h1>{$code}</h1>
    <p>{$message}</p>
    <p><a href='" . url('/') . "'>Go Home</a></p>
</body>
</html>";
        exit;
    }
}
