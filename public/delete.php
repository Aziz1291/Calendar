<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../src/Calendar/bootstrap.php';
require_once '../src/Calendar/Events.php';
require_once '../src/Calendar/Event.php';
require_once '../src/Calendar/User.php';

if (!is_authenticated()) {
    header('Location: login.php');
    exit;
}

$user = current_user();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$from = $_GET['from'] ?? '';

if ($id > 0) {
    $eventsModel = new \Calendar\Events(get_pdo());
    try {
        $event = $eventsModel->find($id);
        if ($event) {
            // Admins can delete any event; regular users can only delete their own pending events
            if ($user->isAdmin() || ($event->getUserId() == $user->getId() && $event->getStatus() === 'Pending')) {
                $eventsModel->delete($id);
                $_SESSION['success'] = "Event '{$event->getName()}' was successfully deleted.";
            } else {
                $_SESSION['error'] = "You do not have permission to delete this event.";
            }
        }
    } catch (\Exception $e) {
        $_SESSION['error'] = "Event not found.";
    }
}

if ($from === 'eventsTable') {
    header('Location: eventsTable.php');
} else {
    header('Location: index.php');
}
exit;
