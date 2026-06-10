<?php
require_once 'bootstrap.php';
require_once 'User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Basic validation
    if (empty($username) || empty($email) || empty($password)) {
        header('Location: ../../public/register.php?error=empty_fields');
        exit();
    }

    $user = new \Calendar\User();

    // Attempt to register
    if ($user->register($username, $email, $password)) {
        header('Location: ../../public/login.php?success=registered');
        exit();
    } else {
        // Registration failed (likely username or email exists)
        header('Location: ../../public/register.php?error=exists');
        exit();
    }
} else {
    header('Location: ../../public/register.php');
    exit();
}
