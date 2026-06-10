<?php
require_once('../src/Calendar/bootstrap.php');
require_once('../src/Calendar/Events.php');
require_once('../src/Calendar/Event.php');

$event = (new \Calendar\Events(get_pdo()))->find($_GET['id']);
$event->setStatus('Approved');
$event->setRejectionReason('');
(new \Calendar\Events(get_pdo()))->update($event);
header('Location: eventsTable.php');
exit;
