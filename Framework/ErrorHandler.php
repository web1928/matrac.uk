<?php
namespace Matrac\Framework;

/**
 * ErrorHandler
 * 
 * Centralized error and exception handling with environment-aware display
 * and comprehensive logging.
 */
class ErrorHandler
{
    private string $logPath;

    public function __construct()
    {
        $logsDir = ROOT_PATH . '/logs';
        $filename = date('Y-m-d') . '_errors.log';
        $this->logPath = $logsDir . '/' . $filename;

        // Ensure logs directory exists
        if (!is_dir($logsDir)) {
            mkdir($logsDir, 0755, true);
        }
    }

    /**
     * Handle regular PHP errors (warnings, notices, etc.)
     * @param $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @return bool
     */
    public function handleError(
        $errno,
        string $errstr = 'Unknown Error',
        string $errfile = 'Unknown File',
        int $errline = 0
    ):bool {
        // Don't handle suppressed errors (@)
        if (!(error_reporting() & $errno)) {
            return false;
        }

        // Log the error
        $this->logError('PHP Error [' . $errno . ']', $errstr, $errfile, $errline);

        // Display the error
        $this->showError('PHP Error [' . $errno . ']', $errstr, $errfile, $errline);

        return true;
    }

    /**
     * Handle uncaught exceptions
     */
    public function handleException($exception)
    {
        // Log the exception with stack trace
        $message = $exception->getMessage() ?? 'Uncaught exception';
        $file = $exception->getFile() ?? 'unknown file';
        $line = $exception->getLine() ?? 0;
        $trace = $exception->getTraceAsString();

        $this->logError('Exception', $message, $file, $line, $trace);

        // Display the exception
        $this->showError('Exception', $message, $file, $line, $trace);

    }

    /**
     * Handle fatal errors (called on shutdown)
     */
    public function handleFatalError()
    {
        $error = error_get_last();

        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            // Log the fatal error
            $this->logError(
                'Fatal Error',
                $error['message'] ?? 'Unknown fatal error',
                $error['file'] ?? 'unknown file',
                $error['line'] ?? 0
            );

            // Display the fatal error
            $this->showError(
                'Fatal Error',
                $error['message'] ?? 'Unknown fatal error',
                $error['file'] ?? 'unknown file',
                $error['line'] ?? 0
            );
        }
    }

    /**
     * Log error to file with context
     */
    private function logError($type, $message, $file, $line, $trace = null)
    {
        // Gather request context
        $context = [
            'url' => $_SERVER['REQUEST_URI'] ?? 'CLI',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
            'user_id' => $_SESSION['user_id'] ?? 'Guest',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
        ];

        // Build log message
        $logMessage = sprintf(
            "[%s] %s: %s in %s on line %d\n",
            date('Y-m-d H:i:s'),
            $type,
            $message,
            $file,
            $line
        );

        // Add context
        $logMessage .= sprintf(
            "    URL: %s | Method: %s | User: %s | IP: %s\n",
            $context['url'],
            $context['method'],
            $context['user_id'],
            $context['ip']
        );

        // Add stack trace if available
        if ($trace) {
            $logMessage .= "    Stack Trace:\n";
            foreach (explode("\n", $trace) as $traceLine) {
                $logMessage .= "        " . $traceLine . "\n";
            }
        }

        $logMessage .= "\n";

        // Write to log file
        error_log($logMessage, 3, $this->logPath);
    }

    /**
     * Display error based on environment and request type
     */
    private function showError($errorType, $errstr, $errfile, $errline, $trace = null)
    {
        if (getenv('APP_ENV') === 'production') {
            // Production: hide details, suppress all output
            ini_set('display_errors', '0');
            error_reporting(0);

            // Check if this is an API request
            if ($this->isApiRequest()) {
                // Return JSON error
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'error' => 'An unexpected error occurred. Please try again later.'
                ]);
                exit;
            }

            // Show generic HTML error page
            http_response_code(500);
            $code = 500;
            $message = "An unexpected error occurred";

            if (file_exists(ROOT_PATH . '/App/Views/errors/index.php')) {
                require_once ROOT_PATH . '/App/Views/errors/index.php';
            } else {
                echo $this->getGenericErrorHtml();
            }
            exit;
        } else {
            // Development: show detailed error
            echo $this->getDetailedErrorHtml($errorType, $errstr, $errfile, $errline, $trace);
        }
    }

    /**
     * Check if current request is an API request
     */
    private function isApiRequest(): bool
    {
        // Check various indicators that this is an API request
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            || (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)
            || strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false;
    }

    /**
     * Generate detailed error HTML for development
     */
    private function getDetailedErrorHtml($errorType, $errstr, $errfile, $errline, $trace = null): string
    {
        $errFileArray = explode('/', $errfile);
        $c = count($errFileArray);
        $errfile = $errFileArray[$c-2] . '/' . $errFileArray[$c-1];
        $html = "
        <div style='background: #f8d7da; padding: 20px; margin: 10px; border-left: 4px solid #721c24; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif;'>
            <h3 style='color: #721c24; margin-top: 0;'>⚠️ {$errorType}</h3>
            <p><strong>Message:</strong> " . htmlspecialchars($errstr) . "</p>
            <p><strong>File:</strong> " . htmlspecialchars($errfile) . "</p>
            <p><strong>Line:</strong> {$errline}</p>";

        // Add stack trace if available
        if ($trace) {
            $html .= "
            <details style='margin-top: 15px;'>
                <summary style='cursor: pointer; font-weight: bold; color: #721c24;'>📋 Stack Trace</summary>
                <pre style='background: #fff; padding: 15px; overflow-x: auto; border: 1px solid #ddd; border-radius: 4px; margin-top: 10px;'>"
                . htmlspecialchars($trace) .
                "</pre>
            </details>";
        }

        $html .= "
        </div>";

        return $html;
    }

    /**
     * Generate generic error HTML for production
     */
    private function getGenericErrorHtml(): string
    {
        return "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>500 - Server Error</title>
            <style>
                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    background: #f5f5f5;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    margin: 0;
                }
                .error-container {
                    text-align: center;
                    background: white;
                    padding: 40px;
                    border-radius: 8px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                    max-width: 500px;
                }
                h1 {
                    font-size: 72px;
                    margin: 0;
                    color: #dc3545;
                }
                h2 {
                    font-size: 24px;
                    margin: 10px 0;
                    color: #333;
                }
                p {
                    color: #666;
                    line-height: 1.6;
                }
                a {
                    display: inline-block;
                    margin-top: 20px;
                    padding: 10px 20px;
                    background: #007bff;
                    color: white;
                    text-decoration: none;
                    border-radius: 4px;
                }
                a:hover {
                    background: #0056b3;
                }
            </style>
        </head>
        <body>
            <div class='error-container'>
                <h1>500</h1>
                <h2>Something Went Wrong</h2>
                <p>We're sorry, but something unexpected happened. Our team has been notified and we're working to fix the issue.</p>
                <p>Please try again later or contact support if the problem persists.</p>
                <a href='/'>Return to Home</a>
            </div>
        </body>
        </html>";
    }

    /**
     * Register all error handlers
     */
    public function register()
    {
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleFatalError']);
    }
}
