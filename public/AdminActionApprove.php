<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../src/Calendar/bootstrap.php';

if (!is_authenticated()) {
    header('Location: login.php');
    exit;
}

$user = current_user();
if (!$user->isAdmin()) {
    header('Location: eventsTable.php');
    exit;
}

$back = ($_POST['from'] ?? '') === 'dashboard' ? 'dashboard.php' : 'eventsTable.php';
require_post($back);

$eventsModel = new \Calendar\Events(get_pdo());
try {
    $event = $eventsModel->find((int) ($_POST['id'] ?? 0));
} catch (\Exception $e) {
    $event = null;
}

if (!$event) {
    $_SESSION['error'] = 'Request not found.';
} else {
    $event->setStatus('Approved');
    $event->setRejectionReason('');
    $eventsModel->update($event);
    $_SESSION['success'] = "Leave request '{$event->getName()}' has been approved.";
}

header('Location: ' . $back);
exit;
