<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once('../src/Calendar/bootstrap.php');

if (!is_authenticated()) {
    header('Location: login.php');
    exit;
}

$pdo = get_pdo();
$eventsModel = new \Calendar\Events($pdo);

if (!isset($_GET['id'])) {
    e404();
}

try {
    $event = $eventsModel->find((int)$_GET['id']);
} catch (\Exception $e) {
    e404();
}

$user = current_user();
// Someone else's request looks the same as a missing one.
if (!can_view_event($event, $user)) {
    e404();
}
$creator = new \Calendar\User();
if ($event->getUserId()) {
    $creator->getUser($event->getUserId());
}

$startDate = $event->getStart()->format('Y-m-d');
$endDate = $event->getEnd()->format('Y-m-d');
$startTime = $event->getStart()->format('H:i');
$endTime = $event->getEnd()->format('H:i');
$isFullDay = ($startTime === '00:00' && $endTime === '23:59');
$isMultiDay = ($startDate !== $endDate);
$durationFormatted = $event->getFormattedDuration();
$statusToneClass = $event->getStatusToneClass();

render('header.php', ['title' => $event->getName()]);
?>

<div class="form-sheet">
    <div class="mb-3">
        <a href="eventsTable.php" class="text-muted" style="font-size: 0.85rem;">
            &larr; Back to Requests
        </a>
    </div>

    <div class="form-sheet__head d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h1 class="mb-1"><?= h($event->getName()) ?></h1>
            <p class="mb-0">
                <?= (int)$event->getAdminEvent() === 1 ? 'Company event' : 'Leave request' ?>
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="status-pill <?= $event->getStatus() ?> <?= $statusToneClass ?>">
                <?= $event->getStatus() ?>
            </span>
        </div>
    </div>

    <!-- Metadata Grid -->
    <div class="d-flex flex-column gap-3 mb-4">
        <div class="d-flex justify-content-between py-2 border-bottom" style="border-color: var(--line) !important;">
            <span class="text-muted">Dates</span>
            <span class="fw-semibold font-monospace">
                <?php if ($isMultiDay): ?>
                    <?= $event->getStart()->format('M j, Y') ?> &rarr; <?= $event->getEnd()->format('M j, Y') ?>
                <?php else: ?>
                    <?= $event->getStart()->format('l, F j, Y') ?>
                <?php endif; ?>
            </span>
        </div>

        <div class="d-flex justify-content-between py-2 border-bottom" style="border-color: var(--line) !important;">
            <span class="text-muted">Duration</span>
            <span class="fw-semibold font-monospace duration-badge" style="color: var(--ink);">
                <?= $durationFormatted ?>
            </span>
        </div>

        <div class="d-flex justify-content-between py-2 border-bottom" style="border-color: var(--line) !important;">
            <span class="text-muted">Time</span>
            <span class="fw-medium">
                <?= $isFullDay ? 'Full day' : $startTime . ' &ndash; ' . $endTime ?>
            </span>
        </div>

        <div class="d-flex justify-content-between py-2 border-bottom" style="border-color: var(--line) !important;">
            <span class="text-muted">Requested By</span>
            <span class="fw-medium">
                <?= (int)$event->getAdminEvent() === 1 ? 'Management (company event)' : h($creator->getUsername() ?: 'Employee') ?>
            </span>
        </div>

        <?php if ($event->getDescription()): ?>
            <div class="py-2 border-bottom" style="border-color: var(--line) !important;">
                <div class="text-muted mb-1">Notes</div>
                <div class="text-dark" style="white-space: pre-wrap; font-size: 0.92rem;"><?= h($event->getDescription()) ?></div>
            </div>
        <?php endif; ?>

        <?php if ($event->getRejectionReason()): ?>
            <div class="editorial-callout mt-2">
                <div class="fw-bold mb-1">&bull; Reason for rejection</div>
                <div><?= h($event->getRejectionReason()) ?></div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Request timeline -->
    <div class="event-timeline mb-4">
        <div class="event-timeline__title">Progress</div>
        <div class="event-timeline__track">
            <!-- Stage 1 -->
            <div class="event-timeline__item is-complete">
                <div class="event-timeline__marker"></div>
                <div class="event-timeline__content">
                    <div class="event-timeline__step">Submitted</div>
                    <div class="event-timeline__desc">
                        <?= (int)$event->getAdminEvent() === 1 ? 'Published by an admin' : 'Submitted by ' . h($creator->getUsername() ?: 'Employee') ?>
                    </div>
                </div>
            </div>

            <!-- Stage 2 -->
            <div class="event-timeline__item is-complete">
                <div class="event-timeline__marker"></div>
                <div class="event-timeline__content">
                    <div class="event-timeline__step">Dates</div>
                    <div class="event-timeline__desc">
                        <?= $isMultiDay ? $event->getStart()->format('M j, Y') . ' &rarr; ' . $event->getEnd()->format('M j, Y') : $event->getStart()->format('l, F j, Y') ?>
                        &middot; <span class="font-monospace fw-semibold"><?= $durationFormatted ?></span>
                    </div>
                </div>
            </div>

            <!-- Stage 3 -->
            <div class="event-timeline__item is-current <?= $statusToneClass ?>">
                <div class="event-timeline__marker"></div>
                <div class="event-timeline__content">
                    <div class="event-timeline__step d-flex align-items-center gap-2">
                        <span>Status:</span>
                        <span class="status-pill <?= $event->getStatus() ?> <?= $statusToneClass ?>"><?= $event->getStatus() ?></span>
                    </div>
                    <div class="event-timeline__desc">
                        <?php if ($event->getStatus() === 'Pending'): ?>
                            Waiting for an admin to approve or reject it.
                        <?php elseif ($event->getStatus() === 'Approved'): ?>
                            Approved and added to the calendar.
                        <?php elseif ($event->getStatus() === 'Rejected'): ?>
                            Declined by an admin.
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions justify-content-between flex-wrap gap-2">
        <a href="eventsTable.php" class="btn btn-secondary">
            &larr; Back to Requests
        </a>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <?php if ($user->isAdmin() && $event->getStatus() === 'Pending'): ?>
                <?= action_button('AdminActionApprove.php', ['id' => $event->getId(), 'from' => 'eventsTable'], 'Approve Request', 'btn btn-success', 'Approve this request?') ?>
                <a href="AdminActionReject.php?id=<?= $event->getId() ?>&from=eventsTable" class="btn btn-outline-danger">Decline Request</a>
            <?php endif; ?>
            <?php if (can_change_event($event, $user)): ?>
                <a href="edit.php?id=<?= $event->getId() ?>" class="btn btn-primary">Edit Request</a>
                <?= action_button('delete.php', ['id' => $event->getId(), 'from' => 'eventsTable'], 'Delete', 'btn btn-danger', ((int)$event->getAdminEvent() === 1 ? 'Delete this event?' : 'Delete this request?')) ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php render('footer.php'); ?>
