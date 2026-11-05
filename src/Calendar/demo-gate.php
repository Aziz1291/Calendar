<?php
// Applied by PHP before every public PHP entry point in the deployed container.
header('X-Robots-Tag: noindex, nofollow', true);
$page = basename($_SERVER['SCRIPT_NAME'] ?? '');
if ($page === 'privacy.php') { return; }
if (session_status() === PHP_SESSION_NONE) { session_name('CALENDARSESSID'); session_start(); }
if (!empty($_SESSION['demo_acknowledged'])) { return; }
if (empty($_SESSION['demo_token'])) { $_SESSION['demo_token'] = bin2hex(random_bytes(32)); }
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['demo_acknowledge'], $_POST['demo_token']) && is_string($_POST['demo_token']) && hash_equals($_SESSION['demo_token'], $_POST['demo_token'])) {
    session_regenerate_id(true);
    $_SESSION['demo_acknowledged'] = true;
    unset($_SESSION['demo_token']);
    header('Location: index.php', true, 303);
    exit;
}
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store');
$token = htmlspecialchars($_SESSION['demo_token'], ENT_QUOTES, 'UTF-8');
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="description" content="Read the Calendar Manager demo notice before using the application."><title>Before you enter | Calendar Manager</title><style>body{font:16px/1.6 system-ui,sans-serif;background:#f3f6fb;color:#172033;margin:0;padding:2rem}main{max-width:40rem;margin:6vh auto;background:white;border-radius:16px;padding:2rem;box-shadow:0 10px 35px #14233a1c}h1{line-height:1.2}button,.leave{display:inline-block;padding:.8rem 1.1rem;border-radius:8px;font:inherit}button{background:#174dc4;color:#fff;border:0;cursor:pointer}.leave{color:#174dc4}a{color:#174dc4}</style></head><body><main><h1>Before you try Calendar Manager</h1><p>This is a demonstration of a scheduling application. Accounts and calendar entries are stored in a database, and administrators can review them. Please use fictional information and a unique password.</p><p>A necessary session cookie remembers your acknowledgement and supports sign-in. The application also loads fonts and styling from external providers. Read the <a href="privacy.php">privacy and cookie notice</a> before continuing.</p><form method="post"><input type="hidden" name="demo_token" value="<?= $token ?>"><button name="demo_acknowledge" value="1" type="submit">I understand and agree</button> <a class="leave" href="https://azizkouki.com/">Leave demo</a></form></main></body></html><?php exit;
