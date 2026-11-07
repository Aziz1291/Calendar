<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../src/Calendar/bootstrap.php';

if (!is_authenticated()) {
    header('Location: login.php');
    exit;
}

$back = ($_POST['from'] ?? '') === 'dashboard' ? 'dashboard.php' : 'eventsTable.php';
require_post($back);

$user = current_user();
$id = (int) ($_POST['id'] ?? 0);
$eventsModel = new \Calendar\Events(get_pdo());
try {
    $event = $eventsModel->find($id);
} catch (\Exception $e) {
    $event = null;
}

// A request the user may not change is reported like a missing one.
if (!$event || !can_change_event($event, $user)) {
    $_SESSION['error'] = 'Event not found.';
} else {
    $eventsModel->delete($id);
    $_SESSION['success'] = "Event '{$event->getName()}' was deleted.";
}

header('Location: ' . $back);
exit;
