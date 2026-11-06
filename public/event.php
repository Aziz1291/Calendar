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
            &larr; Back to Requests Matrix
        </a>
    </div>

    <div class="form-sheet__head d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h1 class="mb-1"><?= h($event->getName()) ?></h1>
            <p class="mb-0">
                <?= (int)$event->getAdminEvent() === 1 ? 'Company Milestone / Official Holiday' : 'Personal Leave Request' ?>
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
            <span class="text-muted">Schedule Span</span>
            <span class="fw-semibold font-monospace">
                <?php if ($isMultiDay): ?>
                    <?= $event->getStart()->format('M j, Y') ?> &rarr; <?= $event->getEnd()->format('M j, Y') ?>
                <?php else: ?>
                    <?= $event->getStart()->format('l, F j, Y') ?>
                <?php endif; ?>
            </span>
        </div>

        <div class="d-flex justify-content-between py-2 border-bottom" style="border-color: var(--line) !important;">
            <span class="text-muted">Formatted Duration</span>
            <span class="fw-semibold font-monospace duration-badge" style="color: var(--ink);">
                <?= $durationFormatted ?>
            </span>
        </div>

        <div class="d-flex justify-content-between py-2 border-bottom" style="border-color: var(--line) !important;">
            <span class="text-muted">Time Schedule</span>
            <span class="fw-medium">
                <?= $isFullDay ? 'Full Working Day' : $startTime . ' &ndash; ' . $endTime ?>
            </span>
        </div>

        <div class="d-flex justify-content-between py-2 border-bottom" style="border-color: var(--line) !important;">
            <span class="text-muted">Requested By</span>
            <span class="fw-medium">
                <?= (int)$event->getAdminEvent() === 1 ? 'Management (Company Wide)' : h($creator->getUsername() ?: 'Employee') ?>
            </span>
        </div>

        <?php if ($event->getDescription()): ?>
            <div class="py-2 border-bottom" style="border-color: var(--line) !important;">
                <div class="text-muted mb-1">Notes &amp; Description</div>
                <div class="text-dark" style="white-space: pre-wrap; font-size: 0.92rem;"><?= h($event->getDescription()) ?></div>
            </div>
        <?php endif; ?>

        <?php if ($event->getRejectionReason()): ?>
            <div class="editorial-callout mt-2">
                <div class="fw-bold mb-1">&bull; Administrative Rejection Rationale</div>
                <div><?= h($event->getRejectionReason()) ?></div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Event Dossier Timeline -->
    <div class="event-timeline mb-4">
        <div class="event-timeline__title">Event Timeline &amp; Lifecycle</div>
        <div class="event-timeline__track">
            <!-- Stage 1 -->
            <div class="event-timeline__item is-complete">
                <div class="event-timeline__marker"></div>
                <div class="event-timeline__content">
                    <div class="event-timeline__step">Submission Initiated</div>
                    <div class="event-timeline__desc">
                        <?= (int)$event->getAdminEvent() === 1 ? 'Published by Administration' : 'Submitted by ' . h($creator->getUsername() ?: 'Employee') ?>
                    </div>
                </div>
            </div>

            <!-- Stage 2 -->
            <div class="event-timeline__item is-complete">
                <div class="event-timeline__marker"></div>
                <div class="event-timeline__content">
                    <div class="event-timeline__step">Scheduled Window &amp; Duration</div>
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
                        <span>Current Status:</span>
                        <span class="status-pill <?= $event->getStatus() ?> <?= $statusToneClass ?>"><?= $event->getStatus() ?></span>
                    </div>
                    <div class="event-timeline__desc">
                        <?php if ($event->getStatus() === 'Pending'): ?>
                            Queued in triage stream awaiting administrative review and approval.
                        <?php elseif ($event->getStatus() === 'Approved'): ?>
                            Formally approved and registered on the workspace schedule.
                        <?php elseif ($event->getStatus() === 'Rejected'): ?>
                            Request declined by administration.
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions justify-content-between flex-wrap gap-2">
        <a href="eventsTable.php" class="btn btn-secondary">
            &larr; Return to Table
        </a>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <?php if ($user->isAdmin() && $event->getStatus() === 'Pending'): ?>
                <a href="AdminActionApprove.php?id=<?= $event->getId() ?>&from=eventsTable" class="btn btn-success">Approve Request</a>
                <a href="AdminActionReject.php?id=<?= $event->getId() ?>&from=eventsTable" class="btn btn-outline-danger">Decline Request</a>
            <?php endif; ?>
            <?php if ($user->isAdmin() || ($event->getUserId() == $user->getId() && $event->getStatus() === 'Pending')): ?>
                <a href="edit.php?id=<?= $event->getId() ?>" class="btn btn-primary">Edit Request</a>
                <a href="delete.php?id=<?= $event->getId() ?>" class="btn btn-danger"
                   onclick="return confirm('Are you sure you want to delete this event?');">Delete</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php render('footer.php'); ?>
