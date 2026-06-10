<?php
require_once('bootstrap.php');
require_once('Events.php');
require_once('Event.php');
$event=(new \Calendar\Events(get_pdo()))->find($_GET['id']);
$event->setStatus('Approved');
$event->setRejectionReason('');
(new \Calendar\Events(get_pdo()))->update($event);
header('Location: ../../public/eventsTable.php');
exit;
