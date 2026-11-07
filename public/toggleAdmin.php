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
    $_SESSION['error'] = 'You cannot change your own role.';
} else {
    // The new role comes from the stored role, not from the request.
    $newRole = $target['role'] === 'admin' ? 'user' : 'admin';
    (new \Calendar\User())->updateRole($userId, $newRole);
    $_SESSION['success'] = "{$target['username']} is now " . ($newRole === 'admin' ? 'an admin.' : 'a regular user.');
}

header('Location: usersTable.php');
exit;
