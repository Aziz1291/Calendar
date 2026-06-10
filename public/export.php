<?php
require '../src/Calendar/bootstrap.php';
$pdo=get_pdo();
    $events = new \Calendar\Events($pdo);
    $month = new \Calendar\Month($_GET['month'] ?? null, $_GET['year'] ?? null);
    $weeks = $month->getWeeks();
    $start=new \DateTime("first day of january");
    $end=(clone $start)->modify("last day of december")->modify("+1 day");
    var_dump($end,$start,$end);
    $events=$events->getEventsBetween($start, $end);