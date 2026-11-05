<?php
session_start();
require_once '../src/Calendar/bootstrap.php';
require_once '../src/Calendar/Events.php';
require_once '../src/Calendar/User.php';

if (!is_authenticated()) {
    header('Location: login.php');
    exit;
}

$pdo = get_pdo();
$user = current_user();
$hasSearch = isset($_GET['search']) && !empty(trim($_GET['search']));
$currentStatus = $_GET['status'] ?? 'all';
$hasStatus = $currentStatus !== 'all';

$eventsModel = new \Calendar\Events($pdo);

if ($user->isAdmin()) {
    if ($hasSearch && $hasStatus) {
        $events = $eventsModel->filterBySearchAndStatus(trim($_GET['search']), $currentStatus);
    } elseif ($hasSearch) {
        $events = $eventsModel->filterBySearch(trim($_GET['search']));
    } elseif ($hasStatus) {
        $events = $eventsModel->filterByStatus($currentStatus);
    } else {
        $events = $eventsModel->getAll();
    }
} else {
    // Non-admin employee: view strictly own submissions with status & search filtering
    $statusParam = $hasStatus ? $currentStatus : null;
    $searchParam = $hasSearch ? trim($_GET['search']) : null;
    $events = $eventsModel->getEmployeeSubmissions($user->getId(), $statusParam, $searchParam);
}

$eventCount = $events ? count($events) : 0;
render('header.php', ['title' => $user->isAdmin() ? 'All Requests' : 'My Requests']);
?>

<div class="data-table-container">
    <!-- Session Feedback Alerts -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success d-flex align-items-center gap-2 m-3 mb-0">
            <span>&check;</span>
            <span><?= h($_SESSION['success']) ?></span>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2 m-3 mb-0">
            <span>&bull;</span>
            <span><?= h($_SESSION['error']) ?></span>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Toolbar -->
    <div class="table-toolbar">
        <div class="table-toolbar__title">
            <h2><?= $user->isAdmin() ? 'Leave &amp; Schedule Matrix' : 'My Leave Requests' ?></h2>
            <span class="count-badge"><?= $eventCount ?> <?= $eventCount === 1 ? 'record' : 'records' ?></span>
        </div>

        <form action="" method="GET" class="table-toolbar__filters d-flex align-items-center gap-2 flex-wrap">
            <input type="text" name="search" id="search"
                   value="<?= h($_GET['search'] ?? '') ?>"
                   placeholder="<?= $user->isAdmin() ? 'Filter by title or employee...' : 'Filter by request reason...' ?>"
                   class="form-control" style="width: 220px; font-size: 0.84rem; padding: 6px 10px;">

            <div class="filter-pills" role="group" aria-label="Status filters">
                <button type="submit" name="status" value="all"
                        class="filter-pill-btn <?= ($currentStatus === 'all') ? 'is-active' : '' ?>">All</button>
                <button type="submit" name="status" value="Pending"
                        class="filter-pill-btn <?= ($currentStatus === 'Pending') ? 'is-active' : '' ?>">Pending</button>
                <button type="submit" name="status" value="Approved"
                        class="filter-pill-btn <?= ($currentStatus === 'Approved') ? 'is-active' : '' ?>">Approved</button>
                <button type="submit" name="status" value="Rejected"
                        class="filter-pill-btn <?= ($currentStatus === 'Rejected') ? 'is-active' : '' ?>">Rejected</button>
            </div>

            <a href="add.php" class="btn btn-primary btn-sm ms-auto">
                <?= $user->isAdmin() ? '+ Create Event' : '+ Request Day Off' ?>
            </a>
        </form>
    </div>

    <!-- Table Body -->
    <?php if (empty($events)): ?>
        <div class="p-5 text-center">
            <div class="text-muted mb-3" style="font-size: 0.95rem;">
                <?= $user->isAdmin()
                    ? 'No leave records found matching your filters.'
                    : ($hasStatus || $hasSearch
                        ? 'No leave requests match the selected status or search filter.'
                        : 'You have not submitted any leave requests yet.') ?>
            </div>
            <a href="add.php" class="btn btn-primary btn-sm">
                <?= $user->isAdmin() ? '+ Create Company Event' : '+ Submit New Request' ?>
            </a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <?php if ($user->isAdmin()): ?>
                            <th>Employee</th>
                        <?php endif; ?>
                        <th>Reason / Title</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event):
                        $requesterName = '';
                        if ($user->isAdmin()) {
                            $eventCreator = new \Calendar\User();
                            $eventCreator->getUser($event->getUserId());
                            $requesterName = $eventCreator->getUsername();
                        }
                        $statusToneClass = $event->getStatusToneClass();
                    ?>
                        <tr>
                            <?php if ($user->isAdmin()): ?>
                                <td>
                                    <div class="avatar-cell">
                                        <span class="avatar-badge"><?= strtoupper(substr($requesterName, 0, 2)) ?></span>
                                        <span class="fw-semibold"><?= h($requesterName) ?></span>
                                    </div>
                                </td>
                            <?php endif; ?>
                            <td>
                                <div class="fw-semibold text-dark"><?= h($event->getName()) ?></div>
                                <?php if ((int)$event->getAdminEvent() === 1): ?>
                                    <span class="badge" style="background: var(--tone-sea-bg); color: var(--tone-sea-text); border: 1px solid var(--tone-sea-border); font-size: 10px;">
                                        Company Wide
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="table-time">
                                <?= $event->getStart()->format('Y-m-d H:i') ?>
                            </td>
                            <td class="table-time">
                                <?= $event->getEnd()->format('Y-m-d H:i') ?>
                            </td>
                            <td class="table-time">
                                <span class="font-monospace" style="font-size: 0.8rem; color: var(--ink-secondary);">
                                    <?= $event->getFormattedDuration() ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-pill <?= $event->getStatus() ?> <?= $statusToneClass ?>">
                                    <?= $event->getStatus() ?>
                                </span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <?php if ($user->isAdmin()): ?>
                                        <?php if ($event->getStatus() === 'Pending'): ?>
                                            <a href="AdminActionApprove.php?id=<?= $event->getId() ?>&from=eventsTable"
                                               class="btn btn-sm btn-success" title="Approve">Approve</a>
                                            <a href="AdminActionReject.php?id=<?= $event->getId() ?>&from=eventsTable"
                                               class="btn btn-sm btn-danger" title="Reject">Reject</a>
                                        <?php elseif ($event->getStatus() === 'Approved'): ?>
                                            <a href="AdminActionReject.php?id=<?= $event->getId() ?>&from=eventsTable"
                                               class="btn btn-sm btn-danger" title="Revoke Approval">Reject</a>
                                        <?php elseif ($event->getStatus() === 'Rejected'): ?>
                                            <a href="AdminActionApprove.php?id=<?= $event->getId() ?>&from=eventsTable"
                                               class="btn btn-sm btn-success" title="Approve Request">Approve</a>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if ((!$user->isAdmin() && $event->getStatus() === 'Pending') || ($user->isAdmin() && (int)$event->getAdminEvent() === 1)): ?>
                                        <a href="edit.php?id=<?= $event->getId() ?>" class="btn btn-sm btn-secondary">Edit</a>
                                        <a href="delete.php?id=<?= $event->getId() ?>&from=eventsTable"
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Are you sure you want to delete this event?');">Delete</a>
                                    <?php endif; ?>

                                    <a href="event.php?id=<?= $event->getId() ?>" class="btn btn-sm btn-ghost">View</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php render('footer.php'); ?>
