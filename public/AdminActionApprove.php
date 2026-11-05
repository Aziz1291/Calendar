<?php
session_start();
require_once('../src/Calendar/bootstrap.php');
require_once('../src/Calendar/Events.php');
require_once('../src/Calendar/Event.php');
require_once('../src/Calendar/User.php');

if (!is_authenticated()) {
    header('Location: login.php');
    exit;
}

$user = current_user();
if (!$user || !$user->isAdmin()) {
    header('Location: eventsTable.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$from = $_GET['from'] ?? '';

if ($id > 0) {
    try {
        $eventsModel = new \Calendar\Events(get_pdo());
        $event = $eventsModel->find($id);
        if ($event) {
            $event->setStatus('Approved');
            $event->setRejectionReason('');
            $eventsModel->update($event);
            $_SESSION['success'] = "Leave request '{$event->getName()}' has been approved.";
        }
    } catch (\Exception $e) {
        $_SESSION['error'] = 'Unable to approve request.';
    }
}

if ($from === 'dashboard') {
    header('Location: dashboard.php');
} else {
    header('Location: eventsTable.php');
}
exit;
