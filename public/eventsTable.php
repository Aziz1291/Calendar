<?php
session_start();
require_once '../src/Calendar/bootstrap.php';
require_once '../src/Calendar/Events.php';
$pdo = get_pdo();
$user = current_user();
$hasSearch = isset($_GET['search']) && !empty($_GET['search']);
$hasStatus = isset($_GET['status']) && $_GET['status'] !== 'all';

if ($user->isAdmin()) {
    if ($hasSearch && $hasStatus) {
        $events = (new \Calendar\Events($pdo))->filterBySearchAndStatus($_GET['search'], $_GET['status']);
    } elseif ($hasSearch) {
        $events = (new \Calendar\Events($pdo))->filterBySearch($_GET['search']);
    } elseif ($hasStatus) {
        $events = (new \Calendar\Events($pdo))->filterByStatus($_GET['status']);
    } else {
        $events = (new \Calendar\Events($pdo))->getAll();
    }
} else {
    $events = (new \Calendar\Events($pdo))->getAllforUser($user->getId());
}

render('header.php');
?>
<div class="container mt-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
        <h1>
            <?= $user->isAdmin() ? 'Day-Off Requests' : 'My Day-Off Requests' ?> <span
                class="badge bg-secondary"><?= $events ? count($events) : 0 ?></span>
        </h1>
        <?php if ($user->isAdmin()): ?>
        <form action="" method="get" class="d-flex flex-wrap align-items-center gap-2">
            <input type="text" name="search" id="search" value="<?= h($_GET['search'] ?? '') ?>" placeholder="Search..."
                class="form-control" style="width: 200px;">
            <div class="btn-group" role="group">
                <input type="radio" class="btn-check" name="status" id="all" value="all" <?= (!isset($_GET['status']) || $_GET['status'] === 'all') ? 'checked' : '' ?>>
                <label class="btn btn-outline-secondary" for="all">All</label>

                <input type="radio" class="btn-check" name="status" id="pending" value="Pending"
                    <?= (isset($_GET['status']) && $_GET['status'] === 'Pending') ? 'checked' : '' ?>>
                <label class="btn btn-outline-warning" for="pending">Pending</label>

                <input type="radio" class="btn-check" name="status" id="approved" value="Approved"
                    <?= (isset($_GET['status']) && $_GET['status'] === 'Approved') ? 'checked' : '' ?>>
                <label class="btn btn-outline-success" for="approved">Approved</label>

                <input type="radio" class="btn-check" name="status" id="rejected" value="Rejected"
                    <?= (isset($_GET['status']) && $_GET['status'] === 'Rejected') ? 'checked' : '' ?>>
                <label class="btn btn-outline-danger" for="rejected">Rejected</label>
            </div>
            <button type="submit" class="btn btn-secondary">Filter</button>
        </form>
        <?php endif; ?>
    </div>

    <?php if (!$events): ?>
        <div class="alert alert-info">
            <?= $user->isAdmin() ? 'No day-off requests found' : 'You have no day-off requests' ?>
        </div>
        <a href="add.php" class="btn btn-primary">
            <?= $user->isAdmin() ? 'Create Company Event' : 'Request Day Off' ?>
        </a>
    <?php else: ?>
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <?php if ($user->isAdmin()): ?>
                        <th>Employee</th>
                    <?php endif; ?>
                    <th>
                        <?= $user->isAdmin() ? 'Request Title' : 'Reason' ?>
                    </th>
                    <th>Start time</th>
                    <th>End time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $event): ?>
                    <tr>
                        <?php if ($user->isAdmin()):
                            $eventCreator = new \Calendar\User();
                            $eventCreator->getUser($event->getUserId());
                            ?>
                            <td><?= $eventCreator->getUsername() ?></td>
                        <?php endif; ?>
                        <td><?= $event->getName() ?></td>
                        <td><?= $event->getStart()->format('Y-m-d H:i') ?></td>
                        <td><?= $event->getEnd()->format('Y-m-d H:i') ?></td>
                        <td><span class="<?= $event->getStatus() ?>"><?= $event->getStatus() ?></span></td>
                        <td>
                            <?php if ((!$user->isAdmin() && $event->getStatus() === 'Pending') || ($user->isAdmin() && (int) $event->getAdminEvent() === 1)): ?>
                                <a href="edit.php?id=<?= $event->getId() ?>" class="btn btn-primary btn-sm">Edit</a>
                                <a href="delete.php?id=<?= $event->getId() ?>" class="btn btn-danger btn-sm">Delete</a>
                            <?php elseif ($user->isAdmin()): ?>
                                <?php if ($event->getStatus() === 'Pending'): ?>
                                    <a href="../src/Calendar/AdminActionApprove.php?id=<?= $event->getId() ?>"
                                        class="btn btn-success btn-sm">Approve</a>
                                    <a href="../src/Calendar/AdminActionReject.php?id=<?= $event->getId() ?>"
                                        class="btn btn-danger btn-sm">Reject</a>
                                <?php elseif ($event->getStatus() === 'Approved'): ?>
                                    <a href="../src/Calendar/AdminActionReject.php?id=<?= $event->getId() ?>"
                                        class="btn btn-danger btn-sm">Reject</a>
                                <?php elseif ($event->getStatus() === 'Rejected'): ?>
                                    <a href="../src/Calendar/AdminActionApprove.php?id=<?= $event->getId() ?>"
                                        class="btn btn-success btn-sm">Approve</a>
                                <?php endif; ?>
                            <?php endif; ?>
                            <a href="event.php?id=<?= $event->getId() ?>" class="btn btn-info btn-sm">View</a>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php render('footer.php'); ?>