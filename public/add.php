<?php
session_start();
require_once '../src/Calendar/bootstrap.php';
$user = current_user();
$data = [
    'start_date' => $_GET['date'] ?? date('Y-m-d'),
    'end_date' => '',
    'start_time' => '',
    'end_time' => '',
];
$validator = new \App\Validator($data);
if (!$validator->validate('start_date', 'date')) {
    $data['start_date'] = date('Y-m-d');
}
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;
    $errors = [];
    $validator = new Calendar\EventValidator($data);
    $errors = $validator->validates($_POST);
    if (empty($errors)) {
        $events = new Calendar\Events(get_pdo());
        $event = $events->hydrate(new Calendar\Event(), $data);
        if (!isset($_SESSION['id'])) {
            header('Location: login.php');
            exit;
        }
        $event->setUserId($_SESSION['id']);
        if ($user->isAdmin()) {
            $event->setAdminEvent(1);
            $event->setStatus('Approved');
        }
        $events->create($event);
        header("location:index.php?success=1");
        exit;
    }
}
$pageTitle = $user->isAdmin() ? 'Create Company Event' : 'Request Day Off';
render('header.php', ['title' => $pageTitle]);
?>
<div class="container">
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <h1><?= $user->isAdmin() ? 'Create Company Event' : 'Request Day Off' ?></h1>
    <?php if (!$user->isAdmin()): ?>
        <p class="text-muted">Submit a request for time off. Select one or more days. Your request will be reviewed by an
            administrator.</p>
    <?php else: ?>
        <p class="text-muted">Create a company-wide event or holiday. All employees will see this.</p>
    <?php endif; ?>
    <form action="" method="POST" class="form">
        <?= render('Calendar/form.php', ['data' => $data, 'errors' => $errors]) ?>
        <div class="form-group">
            <button type="submit"
                class="btn btn-primary"><?= $user->isAdmin() ? 'Create Event' : 'Submit Request' ?></button>
        </div>
    </form>
</div>
<?php render('footer.php'); ?>