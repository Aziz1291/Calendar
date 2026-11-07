<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once('../src/Calendar/bootstrap.php');

if (!is_authenticated()) {
    header('Location: login.php');
    exit;
}

$pdo = get_pdo();
$events = new \Calendar\Events($pdo);
$errors = [];

try {
    $event = $events->find($_GET['id'] ?? null);
} catch (\Exception $e) {
    e404();
} catch (\Error $e) {
    e404();
}
// Employees may only change their own pending requests; anything else looks missing.
if (!can_change_event($event, current_user())) {
    e404();
}

$startDate = $event->getStart()->format('Y-m-d');
$endDate = $event->getEnd()->format('Y-m-d');
$startTime = $event->getStart()->format('H:i');
$endTime = $event->getEnd()->format('H:i');

$isFullDay = ($startTime === '00:00' && $endTime === '23:59');

$data = [
    'name'        => $event->getName(),
    'description' => $event->getDescription(),
    'start_date'  => $startDate,
    'end_date'    => ($startDate !== $endDate) ? $endDate : '',
    'start_time'  => !$isFullDay ? $startTime : '',
    'end_time'    => !$isFullDay ? $endTime : '',
    'status'      => $event->getStatus(),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;
    $validator = new Calendar\EventValidator($data);
    $errors = $validator->validates($data, current_user()->isAdmin());
    if (!csrf_valid()) {
        $errors['form'] = 'Your session expired. Please submit the form again.';
    }
    if (empty($errors)) {
        $events->hydrate($event, $data);
        $events->update($event);
        $_SESSION['success'] = current_user()->isAdmin() ? 'Event updated.' : 'Request updated.';
        header("Location: index.php");
        exit;
    }
}

$user = current_user();
render('header.php', ['title' => 'Edit ' . $event->getName()]);
?>

<div class="form-sheet">
    <div class="mb-3">
        <a href="index.php" class="text-muted" style="font-size: 0.85rem;">
            &larr; Back to Calendar
        </a>
    </div>

    <div class="form-sheet__head d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h1><?= $user->isAdmin() ? 'Modify Company Event' : 'Modify Leave Request' ?></h1>
            <p>Update schedule dates, titles, or operational notes.</p>
        </div>
        <span class="status-pill <?= $event->getStatus() ?> <?= $event->getStatusToneClass() ?>">
            <?= $event->getStatus() ?>
        </span>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <div class="fw-semibold mb-1">Please correct the following errors:</div>
            <ul class="mb-0 ps-3">
                <?php foreach ($errors as $error): ?>
                    <li><?= h($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <?= csrf_field() ?>
        <?= render('calendar/form.php', ['data' => $data, 'errors' => $errors]) ?>

        <div class="form-actions justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <button type="submit" class="btn btn-primary">
                    <?= $user->isAdmin() ? 'Save Changes' : 'Update Request' ?>
                </button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
            <button type="submit" form="delete-event" class="btn btn-danger">Delete Event</button>
        </div>
    </form>
    <form id="delete-event" method="post" action="delete.php"
          onsubmit="return confirm('Are you sure you want to permanently delete this event?');">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $event->getId() ?>">
        <input type="hidden" name="from" value="eventsTable">
    </form>
</div>

<?php render('footer.php'); ?>
