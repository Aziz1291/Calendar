<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../src/Calendar/bootstrap.php';
require_once '../src/Calendar/Events.php';
require_once '../src/Calendar/User.php';

if (!is_authenticated()) {
    header('Location: login.php');
    exit;
}

$pdo = get_pdo();
$eventsModel = new \Calendar\Events($pdo);
$user = current_user();
$isAdmin = $user->isAdmin();

// Retrieve counts by status
$allPending = $eventsModel->filterByStatus('Pending');
$allApproved = $eventsModel->filterByStatus('Approved');
$allRejected = $eventsModel->filterByStatus('Rejected');

if ($isAdmin) {
    $pendingEvents = $allPending;
    $pendingCount = count($allPending);
    $approvedCount = count($allApproved);
    $rejectedCount = count($allRejected);
    $totalCount = $pendingCount + $approvedCount + $rejectedCount;
} else {
    $userId = $user->getId();
    $pendingEvents = array_values(array_filter($allPending, fn($e) => $e->getUserId() == $userId));
    $pendingCount = count($pendingEvents);
    $approvedCount = count(array_filter($allApproved, fn($e) => $e->getUserId() == $userId));
    $rejectedCount = count(array_filter($allRejected, fn($e) => $e->getUserId() == $userId));
    $totalCount = $pendingCount + $approvedCount + $rejectedCount;
}

render('header.php', ['title' => 'Dashboard']);
?>

<div class="dashboard-grid">
    <!-- Session Feedback Alerts -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success d-flex align-items-center gap-2 mb-0">
            <span>&check;</span>
            <span><?= h($_SESSION['success']) ?></span>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-0">
            <span>&bull;</span>
            <span><?= h($_SESSION['error']) ?></span>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Header / Hero Greeting -->
    <div class="dashboard-head">
        <div class="dashboard-head__info">
            <h1 class="mb-0">
                Dashboard
            </h1>
            <p>
                <?= $isAdmin ? 'Pending requests and request totals.' : 'Your requests and their status.' ?>
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="eventsTable.php" class="btn btn-secondary">
                <?= $isAdmin ? 'All Requests' : 'My Requests' ?>
            </a>
            <a href="add.php" class="btn btn-primary">
                <?= $isAdmin ? '+ Create Company Event' : '+ Request Day Off' ?>
            </a>
        </div>
    </div>

    <!-- Request totals -->
    <div class="metrics-strip">
        <div class="metric-card metric-card--pending">
            <div class="metric-card__kicker">Pending</div>
            <div class="metric-card__value"><?= $pendingCount ?></div>
            <div class="metric-card__desc"><?= $isAdmin ? 'Waiting for your decision' : 'Waiting for a decision' ?></div>
        </div>

        <div class="metric-card metric-card--approved">
            <div class="metric-card__kicker">Approved</div>
            <div class="metric-card__value"><?= $approvedCount ?></div>
            <div class="metric-card__desc">Time off granted</div>
        </div>

        <div class="metric-card metric-card--rejected">
            <div class="metric-card__kicker">Rejected</div>
            <div class="metric-card__value"><?= $rejectedCount ?></div>
            <div class="metric-card__desc">Declined by an admin</div>
        </div>

        <div class="metric-card metric-card--total">
            <div class="metric-card__kicker">Total requests</div>
            <div class="metric-card__value"><?= $totalCount ?></div>
            <div class="metric-card__desc"><?= $isAdmin ? 'All employees' : 'All of yours' ?></div>
        </div>
    </div>

    <!-- Split Cockpit Layout -->
    <div class="dashboard-split">
        <!-- Left: Actionable Queue -->
        <div class="dashboard-panel">
            <div class="dashboard-panel__head">
                <h3 class="mb-0">
                    <?= $isAdmin ? 'Pending requests' : 'My pending requests' ?>
                </h3>
                <span class="count-badge"><?= count($pendingEvents) ?> pending</span>
            </div>
            <div class="dashboard-panel__body p-0">
                <?php if (empty($pendingEvents)): ?>
                    <div class="p-4 text-center text-muted" style="font-size: 0.92rem;">
                        No pending requests.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <?php if ($isAdmin): ?>
                                    <th>Employee</th>
                                <?php endif; ?>
                                <th>Request</th>
                                <th>Dates</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($pendingEvents, 0, 5) as $req):
                                $requesterName = '';
                                if ($isAdmin) {
                                    $creator = new \Calendar\User();
                                    $creator->getUser($req->getUserId());
                                    $requesterName = $creator->getUsername();
                                }
                            ?>
                                <tr>
                                    <?php if ($isAdmin): ?>
                                        <td class="fw-semibold" data-label="Employee">
                                            <div class="avatar-cell">
                                                <span class="avatar-badge"><?= strtoupper(substr($requesterName, 0, 2)) ?></span>
                                                <span><?= h($requesterName) ?></span>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                    <td data-label="Request">
                                        <div class="fw-medium text-dark"><?= h($req->getName()) ?></div>
                                    </td>
                                    <td data-label="Dates">
                                        <div class="table-time">
                                            <?= $req->getStart()->format('M j, Y') ?>
                                            <?php if ($req->getStart()->format('Y-m-d') !== $req->getEnd()->format('Y-m-d')): ?>
                                                &rarr; <?= $req->getEnd()->format('M j, Y') ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td data-label="Actions" class="td-actions">
                                        <div class="table-actions">
                                            <?php if ($isAdmin): ?>
                                                <?= action_button('AdminActionApprove.php', ['id' => $req->getId(), 'from' => 'dashboard'], 'Approve', 'btn btn-sm btn-success', 'Approve this request?') ?>
                                                <a href="AdminActionReject.php?id=<?= $req->getId() ?>&from=dashboard"
                                                   class="btn btn-sm btn-danger" title="Reject Request">Reject</a>
                                            <?php else: ?>
                                                <a href="edit.php?id=<?= $req->getId() ?>" class="btn btn-sm btn-secondary">Edit</a>
                                            <?php endif; ?>
                                            <a href="event.php?id=<?= $req->getId() ?>" class="btn btn-sm btn-ghost">View</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (count($pendingEvents) > 5): ?>
                <div class="p-3 text-center border-top" style="border-color: var(--line) !important;">
                    <a href="eventsTable.php?status=Pending" class="btn btn-sm btn-ghost">
                        See all <?= count($pendingEvents) ?> pending requests &rarr;
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right: Operational Tools & Availability -->
        <div class="d-flex flex-column gap-3">
            <div class="dashboard-panel">
                <div class="dashboard-panel__head">
                    <h3 class="mb-0">Go to</h3>
                </div>
                <div class="dashboard-panel__body d-flex flex-column gap-2">
                    <a href="index.php" class="quick-action-link">
                        <span>Calendar</span>
                        <span>&rarr;</span>
                    </a>
                    <a href="eventsTable.php" class="quick-action-link">
                        <span><?= $isAdmin ? 'All Requests' : 'My Requests' ?></span>
                        <span>&rarr;</span>
                    </a>
                    <?php if ($isAdmin): ?>
                        <a href="usersTable.php" class="quick-action-link">
                            <span>Employees</span>
                            <span>&rarr;</span>
                        </a>
                    <?php endif; ?>
                    <a href="add.php" class="quick-action-link">
                        <span><?= $isAdmin ? 'Create Company Event' : 'Request Day Off' ?></span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php render('footer.php'); ?>
