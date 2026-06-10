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

<div class="container mt-4">
    <h1 class="mb-4">
        <?= $isAdmin ? 'Admin Dashboard' : 'My Dashboard' ?>
    </h1>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card text-bg-warning">
                <div class="card-body text-center">
                    <h3 class="display-4">
                        <?= $pendingCount ?>
                    </h3>
                    <p class="mb-0">Pending</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-bg-success">
                <div class="card-body text-center">
                    <h3 class="display-4">
                        <?= $approvedCount ?>
                    </h3>
                    <p class="mb-0">Approved</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-bg-danger">
                <div class="card-body text-center">
                    <h3 class="display-4">
                        <?= $rejectedCount ?>
                    </h3>
                    <p class="mb-0">Rejected</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="d-flex flex-wrap gap-2">
        <a href="add.php" class="btn btn-primary">
            <?= $isAdmin ? '+ Create Company Event' : '+ Request Day Off' ?>
        </a>
        <a href="eventsTable.php" class="btn btn-outline-secondary">
            <?= $isAdmin ? 'View All Requests' : 'My Requests' ?>
        </a>
        <a href="index.php" class="btn btn-outline-primary">Calendar</a>
        <?php if ($isAdmin): ?>
            <a href="usersTable.php" class="btn btn-outline-info">Employees</a>
        <?php endif; ?>
    </div>
</div>

<?php render('footer.php'); ?>