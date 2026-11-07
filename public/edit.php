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
    if (empty($errors)) {
        $events->hydrate($event, $data);
        $events->update($event);
        header("Location: index.php?success=1");
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
        <?= render('calendar/form.php', ['data' => $data, 'errors' => $errors]) ?>

        <div class="form-actions justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <button type="submit" class="btn btn-primary">
                    <?= $user->isAdmin() ? 'Save Changes' : 'Update Request' ?>
                </button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
            <a href="delete.php?id=<?= $event->getId() ?>"
               class="btn btn-danger"
               onclick="return confirm('Are you sure you want to permanently delete this event?');">
                Delete Event
            </a>
        </div>
    </form>
</div>

<?php render('footer.php'); ?>
