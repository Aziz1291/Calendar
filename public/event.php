<?php
require_once('../src/Calendar/bootstrap.php');

$pdo = get_pdo();
$events = new \Calendar\Events($pdo);
if (!isset($_GET['id'])) {
    header('Location: 404.php');
}
try {
    $event = $events->find($_GET['id']);
} catch (\Exception $e) {
    e404();
}
render('header.php', ['title' => $event->getName()]);
?>
<div class="container mt-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="mb-0"><?= h($event->getName()) ?></h3>
            <span class="<?= $event->getStatus() ?>"><?= $event->getStatus() ?></span>
        </div>
        <ul class="list-group list-group-flush">
            <?php
            $startDate = $event->getStart()->format('Y-m-d');
            $endDate = $event->getEnd()->format('Y-m-d');
            $startTime = $event->getStart()->format('H:i');
            $endTime = $event->getEnd()->format('H:i');
            $isFullDay = ($startTime === '00:00' && ($endTime === '23:59'));
            $isMultiDay = $startDate !== $endDate;

            if ($isMultiDay): ?>
                <li class="list-group-item"><strong>From:</strong> <?= $event->getStart()->format('d M Y'); ?></li>
                <li class="list-group-item"><strong>To:</strong> <?= $event->getEnd()->format('d M Y'); ?></li>
            <?php elseif ($isFullDay): ?>
                <li class="list-group-item"><strong>Date:</strong> <?= $event->getStart()->format('d M Y'); ?> (Full day)
                </li>
            <?php else: ?>
                <li class="list-group-item"><strong>Date:</strong> <?= $event->getStart()->format('d M Y'); ?></li>
                <li class="list-group-item"><strong>Time:</strong> <?= $startTime ?> - <?= $endTime ?></li>
            <?php endif; ?>
            <?php if ($event->getDescription()): ?>
                <li class="list-group-item"><strong>Description:</strong> <?= h($event->getDescription()) ?></li>
            <?php endif; ?>
            <?php if ($event->getRejectionReason() !== null && $event->getRejectionReason() !== ''): ?>
                <li class="list-group-item text-danger"><strong>Rejection Reason:</strong>
                    <?= h($event->getRejectionReason()) ?></li>
            <?php endif; ?>
        </ul>
        <div class="card-footer">
            <a href="eventsTable.php" class="btn btn-secondary">← Back to Requests</a>
        </div>
    </div>
</div>
<?php require_once('../views/footer.php'); ?>