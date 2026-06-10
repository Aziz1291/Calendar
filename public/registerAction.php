<?php
require_once '../src/Calendar/bootstrap.php';
require_once '../src/Calendar/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email    = $_POST['email']    ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        header('Location: register.php?error=empty_fields');
        exit();
    }

    $user = new \Calendar\User();

    if ($user->register($username, $email, $password)) {
        header('Location: login.php?success=registered');
        exit();
    } else {
        header('Location: register.php?error=exists');
        exit();
    }
} else {
    header('Location: register.php');
    exit();
}
