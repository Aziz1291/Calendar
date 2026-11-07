<?php
require '../src/Calendar/bootstrap.php';
if (!is_authenticated()) {
    header('Location: login.php');
    exit;
}
if (!current_user()->isAdmin()) {
    http_response_code(403);
    exit('Only administrators can use this page.');
}
$pdo=get_pdo();
    $events = new \Calendar\Events($pdo);
    $month = new \Calendar\Month($_GET['month'] ?? null, $_GET['year'] ?? null);
    $weeks = $month->getWeeks();
    $start=new \DateTime("first day of january");
    $end=(clone $start)->modify("last day of december")->modify("+1 day");
    $events=$events->getEventsBetween($start, $end);