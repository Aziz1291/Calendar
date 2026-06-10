<?php
require_once __DIR__ . '/../../autoload.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
function e404()
{
    http_response_code(404);
    require_once(__DIR__ . '/../../public/404.php');
    exit();
}
/**
 * Bootstrap file to set up database connection and common functions
 * @return \PDO
 */
function get_pdo(): \PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    // First try environment variables (useful for hosting on Render)
    $host = getenv('DB_HOST') ?: null;
    $port = getenv('DB_PORT') ?: null;
    $dbname = getenv('DB_NAME') ?: null;
    $username = getenv('DB_USER') ?: null;
    $password = getenv('DB_PASSWORD') ?: null;
    $ssl_ca = getenv('DB_SSL_CA') ?: null;
    $ssl_verify = getenv('DB_SSL_VERIFY') !== 'false';

    // Fall back to config.php if environment variables are not fully set
    if (!$host || !$username) {
        $configFile = __DIR__ . '/../../config.php';
        if (file_exists($configFile)) {
            $cfg = require $configFile;
            $host = $cfg['host'] ?? null;
            $port = $cfg['port'] ?? null;
            $dbname = $cfg['dbname'] ?? null;
            $username = $cfg['username'] ?? null;
            $password = $cfg['password'] ?? null;
            $ssl_ca = $cfg['ssl_ca'] ?? null;
            $ssl_verify = true;
        } else {
            throw new \RuntimeException('Database configuration not found. Please set database environment variables or create a config.php file.');
        }
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $host,
        $port ?: '3306',
        $dbname ?: 'defaultdb'
    );

    $options = [
        \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_PERSISTENT         => false,
    ];

    // Handle SSL options for Aiven or secure databases
    if ($ssl_ca && file_exists($ssl_ca)) {
        $options[\PDO::MYSQL_ATTR_SSL_CA] = $ssl_ca;
        $options[\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = $ssl_verify;
    } elseif (getenv('DB_SSL') === 'true' || strpos($host, 'aivencloud.com') !== false) {
        // If it's an Aiven host or DB_SSL is requested, initiate an encrypted connection
        // without verifying the server certificate file path.
        $options[\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }

    $pdo = new \PDO($dsn, $username, $password, $options);
    return $pdo;
}
/**
 * Escape HTML special characters in a string
 * @return string
 * @param string $string
 */
function h(string $string): string
{
    if ($string === null) {
        return '';
    }
    return htmlentities($string);
}
function render(string $view, array $params = []): void
{
    extract($params);
    require_once __DIR__ . '/../../views/' . $view;
}



function is_authenticated(): bool
{
    return isset($_SESSION['id']);
}

function current_user(): ?\Calendar\User
{
    if (!isset($_SESSION['id'])) {
        return null;
    }
    require_once __DIR__ . '/User.php';
    $userModel = new \Calendar\User();
    $userModel->getUser($_SESSION['id']);
    return $userModel;
}
?>