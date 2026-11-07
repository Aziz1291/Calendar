<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../src/Calendar/bootstrap.php';

$currentUser = current_user();
if (!$currentUser || !$currentUser->isAdmin()) {
    header('Location: index.php');
    exit;
}
require_post('usersTable.php');

$userId = (int) ($_POST['id'] ?? 0);
$target = (new \Calendar\User())->getUser($userId);

if (!$target) {
    $_SESSION['error'] = 'User not found.';
} elseif ($userId === (int) $currentUser->getId()) {
    $_SESSION['error'] = 'You cannot delete your own account.';
} else {
    (new \Calendar\User())->delete($userId);
    $_SESSION['success'] = "User '{$target['username']}' was deleted.";
}

header('Location: usersTable.php');
exit;
