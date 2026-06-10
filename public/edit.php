<?php
require_once('../src/Calendar/bootstrap.php');

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

$isFullDay = ($startTime === '00:00' && ($endTime === '23:59' || $endTime === '23:59'));

$data = [
    'name' => $event->getName(),
    'description' => $event->getDescription(),
    'start_date' => $startDate,
    'end_date' => ($startDate !== $endDate) ? $endDate : '',
    'start_time' => !$isFullDay ? $startTime : '',
    'end_time' => !$isFullDay ? $endTime : '',
    'status' => $event->getStatus(),
];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;
    $validator = new Calendar\EventValidator($data);
    $errors = $validator->validates($data);
    if (empty($errors)) {
        $events->hydrate($event, $data);
        $events->update($event);
        header("location:index.php?success=1");
        exit;
    }
}

$user = current_user();
render('header.php', ['title' => $event->getName()]);
?>
<div class="container">
    <h1><?= $user->isAdmin() ? 'Edit Company Event' : 'Edit Day-Off Request' ?>
        <small><?= h($event->getName()) ?></small>
    </h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="" method="POST" class="form">
        <?= render('Calendar/form.php', ['data' => $data, 'errors' => $errors]) ?>
        <div class="form-group">
            <button type="submit"
                class="btn btn-primary"><?= $user->isAdmin() ? 'Update Event' : 'Update Request' ?></button>
            <a href="delete.php?id=<?= $event->getId() ?>" class="btn btn-danger">Delete</a>
        </div>

    </form>
</div>
<?php render('footer.php'); ?>