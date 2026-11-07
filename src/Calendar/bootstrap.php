<?php
require_once __DIR__ . '/error-page.php';
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

/** One token per session, sent with every form that changes data. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    return !empty($_SESSION['csrf'])
        && isset($_POST['csrf_token'])
        && hash_equals($_SESSION['csrf'], (string) $_POST['csrf_token']);
}

/** Actions that change data only run from a POST form with the session token. */
function require_post(string $back): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid()) {
        $_SESSION['error'] = 'That action only works from its button. Please try again.';
        header('Location: ' . $back);
        exit;
    }
}

/** A one-button form, used instead of a link for actions that change data. */
function action_button(string $action, array $fields, string $label, string $class, ?string $confirm = null): string
{
    $html = '<form method="post" action="' . h($action) . '" class="d-inline"';
    if ($confirm !== null) {
        $html .= ' onsubmit="return confirm(' . h(json_encode($confirm)) . ');"';
    }
    $html .= '>' . csrf_field();
    foreach ($fields as $name => $value) {
        $html .= '<input type="hidden" name="' . h($name) . '" value="' . h((string) $value) . '">';
    }
    return $html . '<button type="submit" class="' . h($class) . '">' . h($label) . '</button></form>';
}

/** Admins see everything, everyone sees company events, employees see their own requests. */
function can_view_event(\Calendar\Event $event, \Calendar\User $user): bool
{
    return $user->isAdmin()
        || (int) $event->getAdminEvent() === 1
        || (int) $event->getUserId() === (int) $user->getId();
}

/** Admins change anything; employees change only their own pending requests. */
function can_change_event(\Calendar\Event $event, \Calendar\User $user): bool
{
    return $user->isAdmin()
        || ((int) $event->getUserId() === (int) $user->getId() && $event->getStatus() === 'Pending');
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