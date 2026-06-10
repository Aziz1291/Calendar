<?php
session_start();
require_once '../src/Calendar/bootstrap.php';

$currentUser = current_user();

// Only admins can delete users
if (!$currentUser || !$currentUser->isAdmin()) {
    header('Location: usersTable.php');
    exit;
}

$userId = $_GET['id'] ?? null;

if ($userId) {
    // Prevent admin from deleting themselves
    if ($userId == $currentUser->getId()) {
        $_SESSION['error'] = 'You cannot delete your own account.';
        header('Location: usersTable.php');
        exit;
    }

    (new \Calendar\User())->delete($userId);
    $_SESSION['success'] = 'User deleted successfully.';
}

header('Location: usersTable.php');
exit;
