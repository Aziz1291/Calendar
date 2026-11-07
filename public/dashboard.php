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
                <?= $isAdmin ? 'Team Leave & Scheduling Cockpit' : 'My Leave & Schedule Hub' ?>
            </h1>
            <p>
                <?= $isAdmin
                    ? 'Review team leave submissions, manage company milestones, and audit active requests.'
                    : 'Track your pending requests, scheduled time off, and company-wide calendar events.' ?>
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="eventsTable.php" class="btn btn-secondary">
                <?= $isAdmin ? 'View All Requests' : 'My Requests' ?>
            </a>
            <a href="add.php" class="btn btn-primary">
                <?= $isAdmin ? '+ Create Company Event' : '+ Request Day Off' ?>
            </a>
        </div>
    </div>

    <!-- 4-Tile Editorial Metrics Strip -->
    <div class="metrics-strip">
        <div class="metric-card metric-card--pending">
            <div class="metric-card__kicker">Pending Reviews</div>
            <div class="metric-card__value"><?= $pendingCount ?></div>
            <div class="metric-card__desc"><?= $isAdmin ? 'Awaiting administrative triage' : 'Under active review' ?></div>
        </div>

        <div class="metric-card metric-card--approved">
            <div class="metric-card__kicker">Approved Leaves</div>
            <div class="metric-card__value"><?= $approvedCount ?></div>
            <div class="metric-card__desc">Confirmed on official schedule</div>
        </div>

        <div class="metric-card metric-card--rejected">
            <div class="metric-card__kicker">Rejected Requests</div>
            <div class="metric-card__value"><?= $rejectedCount ?></div>
            <div class="metric-card__desc">Declined with stated rationale</div>
        </div>

        <div class="metric-card metric-card--total">
            <div class="metric-card__kicker">Total Events Recorded</div>
            <div class="metric-card__value"><?= $totalCount ?></div>
            <div class="metric-card__desc"><?= $isAdmin ? 'Cumulative organization log' : 'Your personal lifetime logs' ?></div>
        </div>
    </div>

    <!-- Split Cockpit Layout -->
    <div class="dashboard-split">
        <!-- Left: Actionable Queue -->
        <div class="dashboard-panel">
            <div class="dashboard-panel__head">
                <h3 class="mb-0">
                    <?= $isAdmin ? 'Pending Approval Stream' : 'My Active Queue' ?>
                </h3>
                <span class="count-badge"><?= count($pendingEvents) ?> pending</span>
            </div>
            <div class="dashboard-panel__body p-0">
                <?php if (empty($pendingEvents)): ?>
                    <div class="p-4 text-center text-muted" style="font-size: 0.92rem;">
                        No pending requests in the review queue. All schedules are up to date!
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <?php if ($isAdmin): ?>
                                    <th>Employee</th>
                                <?php endif; ?>
                                <th>Reason / Title</th>
                                <th>Schedule Span</th>
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
                                        <td class="fw-semibold">
                                            <div class="avatar-cell">
                                                <span class="avatar-badge"><?= strtoupper(substr($requesterName, 0, 2)) ?></span>
                                                <span><?= h($requesterName) ?></span>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                    <td>
                                        <div class="fw-medium text-dark"><?= h($req->getName()) ?></div>
                                    </td>
                                    <td>
                                        <div class="table-time">
                                            <?= $req->getStart()->format('M j, Y') ?>
                                            <?php if ($req->getStart()->format('Y-m-d') !== $req->getEnd()->format('Y-m-d')): ?>
                                                &rarr; <?= $req->getEnd()->format('M j, Y') ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
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
                        View All <?= count($pendingEvents) ?> Pending Requests &rarr;
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right: Operational Tools & Availability -->
        <div class="d-flex flex-column gap-3">
            <div class="dashboard-panel">
                <div class="dashboard-panel__head">
                    <h3 class="mb-0">Workspace Navigation</h3>
                </div>
                <div class="dashboard-panel__body d-flex flex-column gap-2">
                    <a href="index.php" class="quick-action-link">
                        <span>Monthly Architectural Calendar</span>
                        <span>&rarr;</span>
                    </a>
                    <a href="eventsTable.php" class="quick-action-link">
                        <span>📋 <?= $isAdmin ? 'All Leave Requests Matrix' : 'My Requests Archive' ?></span>
                        <span>&rarr;</span>
                    </a>
                    <?php if ($isAdmin): ?>
                        <a href="usersTable.php" class="quick-action-link">
                            <span>👥 Employee Directory &amp; Roles</span>
                            <span>&rarr;</span>
                        </a>
                    <?php endif; ?>
                    <a href="add.php" class="quick-action-link">
                        <span>✏️ <?= $isAdmin ? 'Schedule Company Milestone' : 'Submit Day-Off Request' ?></span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>

            <div class="dashboard-panel">
                <div class="dashboard-panel__head">
                    <h3 class="mb-0">System Information</h3>
                </div>
                <div class="dashboard-panel__body" style="font-size: 0.85rem; color: var(--muted);">
                    <div class="d-flex justify-content-between py-1 border-bottom" style="border-color: var(--line) !important;">
                        <span>Session User</span>
                        <strong class="text-dark"><?= h($user->getUsername()) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom" style="border-color: var(--line) !important;">
                        <span>Access Level</span>
                        <span class="role-tag <?= $isAdmin ? 'role-tag--admin' : 'role-tag--user' ?>"><?= h($user->getRole()) ?></span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span>Environment</span>
                        <span class="font-monospace">PHP / MySQL SSL</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php render('footer.php'); ?>
