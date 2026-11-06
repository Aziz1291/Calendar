<?php
// Applied by PHP before every public PHP entry point in the deployed container.
header('X-Robots-Tag: noindex, nofollow', true);
$page = basename($_SERVER['SCRIPT_NAME'] ?? '');
if ($page === 'privacy.php') { return; }
if (session_status() === PHP_SESSION_NONE) { session_name('CALENDARSESSID'); session_start(); }
if (!empty($_SESSION['demo_acknowledged'])) { return; }
if (empty($_SESSION['demo_token'])) { $_SESSION['demo_token'] = bin2hex(random_bytes(32)); }
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['demo_acknowledge'], $_POST['demo_token'], $_POST['privacy_agree']) && is_string($_POST['demo_token']) && hash_equals($_SESSION['demo_token'], $_POST['demo_token'])) {
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
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="description" content="Read the Calendar Manager demo notice before using the application."><meta property="og:type" content="website"><meta property="og:title" content="Calendar Manager demo"><meta property="og:description" content="A scheduling and time-off request demo."><meta property="og:url" content="https://azizkouki.com/projects/calendar"><meta property="og:image" content="https://azizkouki.com/projects/calendar/preview.jpg"><meta name="twitter:card" content="summary_large_image"><title>Before you enter | Calendar Manager</title><style>body{font:16px/1.6 system-ui,sans-serif;background:rgba(15,23,42,.72);color:#172033;margin:0;padding:1rem 1rem 12rem}main{max-width:480px;margin:0 auto;background:white;border-radius:16px;padding:1.25rem;box-shadow:0 10px 35px #14233a1c}h1{line-height:1.2}button,.leave{display:inline-block;padding:.8rem 1.1rem;border-radius:8px;font:inherit}button{background:#2563eb;color:#fff;border:0;cursor:pointer}.leave{color:#2563eb}a{color:#2563eb}</style></head><body><main role="dialog" aria-modal="true" aria-labelledby="demo-title"><h1 id="demo-title">Before you try Calendar Manager</h1><p>This is a demonstration of a scheduling application. Accounts and requests are stored and can be reviewed by administrators. Use fictional information and a unique password.</p><p>Read the <a href="privacy.php">privacy and cookie notice</a> before continuing.</p><form method="post"><input type="hidden" name="demo_token" value="<?= $token ?>"><p><label><input type="checkbox" name="privacy_agree" value="1" required> I have read and agree to the <a href="privacy.php">privacy notice</a>.</label></p><button name="demo_acknowledge" value="1" type="submit">I understand and agree</button> <a class="leave" href="https://azizkouki.com/">Leave demo</a></form></main><aside aria-label="Cookie notice" style="position:fixed;bottom:0;left:0;right:0;background:#fff;color:#172033;padding:.7rem 1rem;text-align:center;font-size:.85rem;box-shadow:0 -2px 12px #0002">A necessary session cookie is already in use to remember this acknowledgement and support sign-in. <a href="privacy.php" style="color:#2563eb">Learn more</a> <button type="button" onclick="this.parentElement.hidden=true" style="padding:.45rem .75rem;margin-left:.5rem">Accept necessary cookies</button></aside></body></html><?php exit;

