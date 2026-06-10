<?php
require_once('../src/Calendar/bootstrap.php');
require_once('../src/Calendar/Events.php');
require_once('../src/Calendar/Event.php');

$events = new \Calendar\Events(get_pdo());
$event  = $events->find($_GET['id'] ?? $_POST['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event->setRejectionReason($_POST['rejectionReason']);
    $event->setStatus('Rejected');
    $events->update($event);
    header('Location: eventsTable.php');
    exit;
}
render('header.php', ['title' => $event->getName()]);
?>

<div class="container">
<h1>Reject Event: <?= htmlspecialchars($event->getName()) ?></h1>
<form action="" method="POST">
<input type="hidden" name="id" value="<?= $event->getId() ?>">
<div class="form-group">
<label for="rejectionReason">Reason for Rejection</label>
<textarea name="rejectionReason" id="rejectionReason" class="form-control" required></textarea>
</div>
<button type="submit" class="btn btn-danger">Reject Event</button>
<a href="eventsTable.php" class="btn btn-secondary">Cancel</a>
</form>
</div>
