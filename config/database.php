<?php

/**
 * Database Configuration
 * 
 * Establishes secure PDO connection to MariaDB
 * Uses prepared statements to prevent SQL injection
 * 
 * SECURITY: 
 * - Database credentials are loaded from .env file
 * - .env file must be in .gitignore (never commit credentials)
 * - Use .env.example as a template for new environments
 * - Set .env file permissions to 600 (read/write owner only)
 * - config/ directory protected by .htaccess (deny from all)
 */

// Database credentials from environment variables
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'database');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');

/**
 * Get Database Connection
 * 
 * @return PDO Database connection object
 * @throws PDOException if connection fails
 */
function getDbConnection()
{
    static $pdo = null;

    // Singleton pattern - reuse existing connection
    if ($pdo !== null) {
        return $pdo;
    }

    try {
        $dsn = sprintf(
            "mysql:host=%s;dbname=%s;charset=%s",
            DB_HOST,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            // Throw exceptions on errors (easier to debug)
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

            // Return associative arrays by default
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

            // Disable emulated prepared statements (true prepared statements)
            PDO::ATTR_EMULATE_PREPARES => false,

            // Set connection timeout
            PDO::ATTR_TIMEOUT => 5,
        ];

        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

        return $pdo;
    } catch (PDOException $e) {
        // Log error securely (don't expose credentials)
        error_log("Database Connection Error: " . $e->getMessage());

        // Display generic error to user (never expose DB details)
        die("Database connection failed. Please contact support.");
    }
}

/**
 * Execute a prepared statement safely
 * 
 * @param string $sql SQL query with placeholders
 * @param array $params Parameters to bind
 * @return PDOStatement Executed statement
 */
function executeQuery($sql, $params = [])
{
    $pdo = getDbConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Get the last inserted ID
 * 
 * @return string Last insert ID
 */
function getLastInsertId()
{
    $pdo = getDbConnection();
    return $pdo->lastInsertId();
}

/**
 * Begin database transaction
 */
function beginTransaction()
{
    $pdo = getDbConnection();
    $pdo->beginTransaction();
}

/**
 * Commit database transaction
 */
function commitTransaction()
{
    $pdo = getDbConnection();
    $pdo->commit();
}

/**
 * Rollback database transaction
 */
function rollbackTransaction()
{
    $pdo = getDbConnection();
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
}
