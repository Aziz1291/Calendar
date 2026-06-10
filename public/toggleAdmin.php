<?php
session_start();
require_once '../src/Calendar/bootstrap.php';

$currentUser = current_user();

// Only admins can change roles
if (!$currentUser || !$currentUser->isAdmin()) {
    header('Location: usersTable.php');
    exit;
}

$userId = $_GET['id'] ?? null;
$currentRole = $_GET['role'] ?? 'user';

if ($userId) {
    // Prevent admin from demoting themselves
    if ($userId == $currentUser->getId()) {
        $_SESSION['error'] = 'You cannot change your own role.';
        header('Location: usersTable.php');
        exit;
    }

    // Toggle role
    $newRole = ($currentRole === 'admin') ? 'user' : 'admin';
    (new \Calendar\User())->updateRole($userId, $newRole);
    $_SESSION['success'] = 'User role updated successfully.';
}

header('Location: usersTable.php');
exit;
