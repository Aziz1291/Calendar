<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../src/Calendar/bootstrap.php';
require_once '../src/Calendar/Events.php';
require_once '../src/Calendar/User.php';

if (!is_authenticated()) {
    header('Location: login.php');
    exit;
}

$currentUser = current_user();
if (!$currentUser || !$currentUser->isAdmin()) {
    header('Location: index.php');
    exit;
}

$pdo = get_pdo();
$users = (new \Calendar\User())->getAll();
$eventsModel = new \Calendar\Events($pdo);
$userCount = $users ? count($users) : 0;

render('header.php', ['title' => 'Employee Directory']);
?>

<div class="data-table-container">
    <!-- Session Feedback Alerts -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success m-3 mb-0">
            &check; <?= h($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger m-3 mb-0">
            &bull; <?= h($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Toolbar -->
    <div class="table-toolbar">
        <div class="table-toolbar__title">
            <h2>Employee Directory &amp; Roles</h2>
            <span class="count-badge"><?= $userCount ?> <?= $userCount === 1 ? 'member' : 'members' ?></span>
        </div>
        <div>
            <a href="register.php" class="btn btn-primary btn-sm">+ Register New Employee</a>
        </div>
    </div>

    <!-- Directory Table -->
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Email Address</th>
                    <th>Access Role</th>
                    <th>Recorded Events</th>
                    <th>Management Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u):
                    $eventCount = $eventsModel->countByUserId($u['id']);
                    $isSelf = ($u['id'] == $currentUser->getId());
                    $isAdminRole = ($u['role'] === 'admin');
                ?>
                    <tr>
                        <td>
                            <div class="avatar-cell">
                                <span class="avatar-badge"><?= strtoupper(substr($u['username'], 0, 2)) ?></span>
                                <span class="fw-semibold text-dark"><?= h($u['username']) ?></span>
                            </div>
                        </td>
                        <td class="font-monospace text-muted" style="font-size: 0.82rem;">
                            <?= h($u['email']) ?>
                        </td>
                        <td>
                            <span class="role-tag <?= $isAdminRole ? 'role-tag--admin' : 'role-tag--user' ?>">
                                <?= h($u['role']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="font-monospace text-muted" style="font-size: 0.84rem;">
                                <?= $eventCount ?> <?= $eventCount === 1 ? 'event' : 'events' ?>
                            </span>
                        </td>
                        <td>
                            <div class="table-actions">
                                <a href="eventsTable.php?search=<?= urlencode($u['username']) ?>"
                                   class="btn btn-sm btn-ghost" title="Filter by user">
                                    View Events
                                </a>

                                <?php if (!$isSelf): ?>
                                    <?php if ($isAdminRole): ?>
                                        <a href="toggleAdmin.php?id=<?= $u['id'] ?>&role=<?= $u['role'] ?>"
                                           class="btn btn-sm btn-secondary"
                                           onclick="return confirm('Revoke administrator privileges from <?= h($u['username']) ?>?');">
                                            Demote to User
                                        </a>
                                    <?php else: ?>
                                        <a href="toggleAdmin.php?id=<?= $u['id'] ?>&role=<?= $u['role'] ?>"
                                           class="btn btn-sm btn-outline-primary"
                                           onclick="return confirm('Promote <?= h($u['username']) ?> to administrator?');">
                                            Promote to Admin
                                        </a>
                                    <?php endif; ?>

                                    <a href="deleteUser.php?id=<?= $u['id'] ?>"
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Permanently remove employee <?= h($u['username']) ?>? All associated leaves will be deleted.');">
                                        Delete
                                    </a>
                                <?php else: ?>
                                    <span class="badge" style="background: var(--tint); color: var(--muted); border: 1px solid var(--line); font-size: 11px;">
                                        Current Session
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php render('footer.php'); ?>
