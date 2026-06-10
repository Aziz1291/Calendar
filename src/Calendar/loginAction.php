<?php
require_once 'bootstrap.php';
require_once 'User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $user = new \Calendar\User();
    if ($user->login($email, $password)) {
        header('Location: ../../public/index.php');
        exit();
    } else {
        header('Location: ../../public/login.php?error=1');
        exit();
    }
} else {
    header('Location: ../../public/login.php');
    exit();
}
