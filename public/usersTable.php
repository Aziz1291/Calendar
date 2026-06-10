<?php
session_start();
require_once '../src/Calendar/bootstrap.php';
require_once '../src/Calendar/Events.php';
$pdo = get_pdo();
$currentUser = current_user();
$users = (new \Calendar\User())->getAll();
render('header.php');
?>
<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success mx-sm-3"><?= $_SESSION['success'] ?></div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger mx-sm-3"><?= $_SESSION['error'] ?></div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>
<div class="d-flex flex-row align-items-center justify-content-between mx-sm-3">
    <h1>Users</h1>
    <h2>users number: <?= count($users) ?></h2>
</div>
<table>
    <thead>
        <tr>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <?php if ($currentUser->isAdmin()): ?>
                <th>Events Count</th>
                <th>Actions</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php
        $eventsModel = new \Calendar\Events($pdo);
        foreach ($users as $user):
            $eventCount = $eventsModel->countByUserId($user['id']);
            $isCurrentUser = ($user['id'] == $currentUser->getId());
            ?>
            <tr>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td>
                    <span class="badge <?= $user['role'] === 'admin' ? 'bg-primary' : 'bg-secondary' ?>">
                        <?= htmlspecialchars($user['role']) ?>
                    </span>
                </td>
                <?php if ($currentUser->isAdmin()): ?>
                    <td><?= $eventCount ?></td>
                    <td>
                        <div class="btn-group" role="group">
                            <a href="eventsTable.php?search=<?= urlencode($user['username']) ?>"
                                class="btn btn-primary btn-sm">View Events</a>
                            <?php if (!$isCurrentUser): ?>
                                <?php if ($user['role'] === 'admin'): ?>
                                    <a href="toggleAdmin.php?id=<?= $user['id'] ?>&role=<?= $user['role'] ?>"
                                        class="btn btn-warning btn-sm"
                                        onclick="return confirm('Are you sure you want to remove admin rights from this user?');">
                                        Remove Admin
                                    </a>
                                <?php else: ?>
                                    <a href="toggleAdmin.php?id=<?= $user['id'] ?>&role=<?= $user['role'] ?>"
                                        class="btn btn-success btn-sm"
                                        onclick="return confirm('Are you sure you want to make this user an admin?');">
                                        Make Admin
                                    </a>
                                <?php endif; ?>
                                <a href="deleteUser.php?id=<?= $user['id'] ?>" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                    Delete
                                </a>
                            <?php else: ?>
                                <span class="badge bg-info">Current User</span>
                            <?php endif; ?>
                        </div>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php render('footer.php'); ?>