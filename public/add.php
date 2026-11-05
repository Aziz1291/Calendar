<?php
session_start();
require_once '../src/Calendar/bootstrap.php';

if (!is_authenticated()) {
    header('Location: login.php');
    exit;
}

$user = current_user();
$data = [
    'start_date' => $_GET['date'] ?? date('Y-m-d'),
    'end_date'   => '',
    'start_time' => '',
    'end_time'   => '',
];

$validator = new \App\Validator($data);
if (!$validator->validate('start_date', 'date')) {
    $data['start_date'] = date('Y-m-d');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;
    $validator = new Calendar\EventValidator($data);
    $errors = $validator->validates($_POST);
    if (empty($errors)) {
        $events = new Calendar\Events(get_pdo());
        $event = $events->hydrate(new Calendar\Event(), $data);
        $event->setUserId($_SESSION['id']);
        if ($user->isAdmin()) {
            $event->setAdminEvent(1);
            $event->setStatus('Approved');
        } else {
            $event->setAdminEvent(0);
            $event->setStatus('Pending');
        }
        $events->create($event);
        header("Location: index.php?success=1");
        exit;
    }
}

$pageTitle = $user->isAdmin() ? 'Create Company Event' : 'Request Day Off';
render('header.php', ['title' => $pageTitle]);
?>

<div class="form-sheet">
    <div class="mb-3">
        <a href="index.php" class="text-muted" style="font-size: 0.85rem;">
            &larr; Back to Calendar
        </a>
    </div>

    <div class="form-sheet__head">
        <h1><?= $user->isAdmin() ? 'Schedule Company Event' : 'Request Leave / Day Off' ?></h1>
        <p>
            <?= $user->isAdmin()
                ? 'Create an organization-wide event, holiday, or offsite meeting visible to all staff.'
                : 'Submit a formal leave request. Multi-day requests are reviewed by an administrator.' ?>
        </p>
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
        <?= render('Calendar/form.php', ['data' => $data, 'errors' => $errors]) ?>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <?= $user->isAdmin() ? 'Publish Company Event' : 'Submit Leave Request' ?>
            </button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php render('footer.php'); ?>
