<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../src/Calendar/bootstrap.php';

// Signing out needs the button, so a link on another site cannot sign people out.
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid()) {
    header('Location: index.php');
    exit;
}
(new \Calendar\User())->logout();
header('Location: login.php');
exit;
