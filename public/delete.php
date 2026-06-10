<?php
require_once '../src/Calendar/bootstrap.php';
require_once '../src/Calendar/Events.php';
(new \Calendar\Events(get_pdo()))->delete($_GET['id']);
header('Location: index.php');
exit;