<?php
require_once '../src/Calendar/bootstrap.php';
require_once '../src/Calendar/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = $_POST['email']    ?? '';
    $password = $_POST['password'] ?? '';

    $user = new \Calendar\User();
    if ($user->login($email, $password)) {
        $currentUser = current_user();
        if ($currentUser && $currentUser->isAdmin()) {
            header('Location: dashboard.php');
        } else {
            header('Location: index.php');
        }
        exit();
    } else {
        header('Location: login.php?error=1');
        exit();
    }
} else {
    header('Location: login.php');
    exit();
}
