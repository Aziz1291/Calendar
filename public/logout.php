<?php
require_once '../src/Calendar/bootstrap.php';
require_once '../src/Calendar/User.php';

$user = new \Calendar\User();
$user->logout();
header('Location: login.php');
exit;
