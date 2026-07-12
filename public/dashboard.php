<?php
session_start();
require_once '../src/Calendar/bootstrap.php';
require_once '../src/Calendar/Events.php';

if (!is_authenticated()) {
    header('Location: login.php');
    exit;
}

$pdo = get_pdo();
$eventsModel = new \Calendar\Events($pdo);
$user = current_user();
$isAdmin = $user->isAdmin();

// Get counts using existing filterByStatus method
$allPending = $eventsModel->filterByStatus('Pending');
$allApproved = $eventsModel->filterByStatus('Approved');
$allRejected = $eventsModel->filterByStatus('Rejected');

if ($isAdmin) {
    $pendingCount = count($allPending);
    $approvedCount = count($allApproved);
    $rejectedCount = count($allRejected);
} else {
    // Filter for current user only
    $userId = $user->getId();
    $pendingCount = count(array_filter($allPending, fn($e) => $e->getUserId() == $userId));
    $approvedCount = count(array_filter($allApproved, fn($e) => $e->getUserId() == $userId));
    $rejectedCount = count(array_filter($allRejected, fn($e) => $e->getUserId() == $userId));
}

render('header.php', ['title' => 'Dashboard']);
?>

<div class="container mt-2" style="max-width:900px;">

    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h1 class="mb-0">
                <?= $isAdmin ? '🛠️ Admin Dashboard' : '👤 My Dashboard' ?>
            </h1>
            <p style="color:var(--color-muted);font-size:0.9rem;margin-top:0.3rem;">
                <?= $isAdmin
                    ? "Overview of all leave requests across your team"
                    : "Track your leave requests and status" ?>
            </p>
        </div>
        <a href="add.php" class="btn btn-primary">
            <?= $isAdmin ? '+ Create Company Event' : '+ Request Day Off' ?>
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <div class="card h-100" style="border-top:4px solid #f59e0b;">
                <div class="card-body d-flex align-items-center gap-3">
                    <div style="font-size:2rem;line-height:1;">⏳</div>
                    <div>
                        <div style="font-size:2rem;font-weight:800;color:#92400e;line-height:1.1;">
                            <?= $pendingCount ?>
                        </div>
                        <div style="font-size:0.8rem;font-weight:600;color:var(--color-muted);text-transform:uppercase;letter-spacing:0.05em;">
                            Pending
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card h-100" style="border-top:4px solid #10b981;">
                <div class="card-body d-flex align-items-center gap-3">
                    <div style="font-size:2rem;line-height:1;">✅</div>
                    <div>
                        <div style="font-size:2rem;font-weight:800;color:#065f46;line-height:1.1;">
                            <?= $approvedCount ?>
                        </div>
                        <div style="font-size:0.8rem;font-weight:600;color:var(--color-muted);text-transform:uppercase;letter-spacing:0.05em;">
                            Approved
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card h-100" style="border-top:4px solid #f43f5e;">
                <div class="card-body d-flex align-items-center gap-3">
                    <div style="font-size:2rem;line-height:1;">❌</div>
                    <div>
                        <div style="font-size:2rem;font-weight:800;color:#9f1239;line-height:1.1;">
                            <?= $rejectedCount ?>
                        </div>
                        <div style="font-size:0.8rem;font-weight:600;color:var(--color-muted);text-transform:uppercase;letter-spacing:0.05em;">
                            Rejected
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card">
        <div class="card-body">
            <h2 style="font-size:1rem;margin-bottom:1rem;color:var(--color-muted);font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">
                Quick Actions
            </h2>
            <div class="d-flex flex-wrap gap-2">
                <a href="eventsTable.php" class="btn btn-outline-secondary">
                    📋 <?= $isAdmin ? 'View All Requests' : 'My Requests' ?>
                </a>
                <a href="index.php" class="btn btn-outline-secondary">
                    📅 Calendar View
                </a>
                <?php if ($isAdmin): ?>
                    <a href="usersTable.php" class="btn btn-outline-secondary">
                        👥 Manage Employees
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<?php render('footer.php'); ?>