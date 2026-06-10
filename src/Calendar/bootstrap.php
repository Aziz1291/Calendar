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

    $configFile = __DIR__ . '/../../config.php';
    if (!file_exists($configFile)) {
        throw new \RuntimeException('config.php not found. Copy config.example.php to config.php and fill in your credentials.');
    }
    $cfg = require $configFile;

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $cfg['host'],
        $cfg['port'],
        $cfg['dbname']
    );

    $options = [
        \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_PERSISTENT         => false,
    ];

    // Aiven requires SSL — attach CA cert if the file exists
    if (!empty($cfg['ssl_ca']) && file_exists($cfg['ssl_ca'])) {
        $options[\PDO::MYSQL_ATTR_SSL_CA]      = $cfg['ssl_ca'];
        $options[\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
    }

    $pdo = new \PDO($dsn, $cfg['username'], $cfg['password'], $options);
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