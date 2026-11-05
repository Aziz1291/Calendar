<?php
$currentScript = basename($_SERVER['PHP_SELF'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Calendar Manager demo for scheduling and reviewing time-off requests.">
    <title><?php echo isset($title) ? h($title) . ' — Calendar Manager' : 'Calendar Manager — Editorial Team Scheduling'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/calendar.css?v=<?= filemtime(__DIR__ . '/../public/css/calendar.css') ?>">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='0.9em' font-size='90'>📅</text></svg>">
</head>
<body><a href="#main-content" style="position:absolute;left:-10000px" onfocus="this.style.left='1rem'" onblur="this.style.left='-10000px'">Skip to content</a>
    <header class="masthead">
        <div class="masthead__inner">
            <div class="d-flex align-items-center gap-3">
                <a href="index.php" class="brand">
                    <span>📅</span>
                    <span>Calendar Manager</span>
                    <span class="brand__badge">Suite</span>
                </a>
                <?php if (is_authenticated()): ?>
                    <?php $navUser = current_user(); ?>
                    <nav class="nav-links d-none d-md-flex">
                        <a href="index.php" class="nav-link <?= ($currentScript === 'index.php') ? 'is-active' : '' ?>">Calendar</a>
                        <a href="dashboard.php" class="nav-link <?= ($currentScript === 'dashboard.php') ? 'is-active' : '' ?>">Dashboard</a>
                        <?php if ($navUser && $navUser->isAdmin()): ?>
                            <a href="eventsTable.php" class="nav-link <?= ($currentScript === 'eventsTable.php') ? 'is-active' : '' ?>">All Requests</a>
                            <a href="usersTable.php" class="nav-link <?= ($currentScript === 'usersTable.php') ? 'is-active' : '' ?>">Employees</a>
                        <?php else: ?>
                            <a href="eventsTable.php" class="nav-link <?= ($currentScript === 'eventsTable.php') ? 'is-active' : '' ?>">My Requests</a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            </div>

            <div class="masthead__actions">
                <?php if (is_authenticated()): ?>
                    <?php $user = current_user(); ?>
                    <div class="user-identity d-none d-sm-inline-flex">
                        <span class="fw-semibold"><?= h($user->getUsername()) ?></span>
                        <span class="role-tag <?= $user->isAdmin() ? 'role-tag--admin' : 'role-tag--user' ?>">
                            <?= h($user->getRole()) ?>
                        </span>
                    </div>
                    <a href="logout.php" class="btn btn-sm btn-ghost">Sign Out</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-sm btn-ghost">Log In</a>
                    <a href="register.php" class="btn btn-sm btn-primary">Create Account</a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (is_authenticated()): ?>
            <!-- Mobile nav drawer -->
            <div class="d-flex d-md-none py-2 gap-1 border-top flex-nowrap" style="border-color: var(--line) !important; overflow-x: auto; -webkit-overflow-scrolling: touch; scrollbar-width: none; white-space: nowrap;">
                <a href="index.php" class="nav-link <?= ($currentScript === 'index.php') ? 'is-active' : '' ?>">Calendar</a>
                <a href="dashboard.php" class="nav-link <?= ($currentScript === 'dashboard.php') ? 'is-active' : '' ?>">Dashboard</a>
                <a href="eventsTable.php" class="nav-link <?= ($currentScript === 'eventsTable.php') ? 'is-active' : '' ?>">
                    <?= ($navUser && $navUser->isAdmin()) ? 'All Requests' : 'My Requests' ?>
                </a>
                <?php if ($navUser && $navUser->isAdmin()): ?>
                    <a href="usersTable.php" class="nav-link <?= ($currentScript === 'usersTable.php') ? 'is-active' : '' ?>">Employees</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </header>
    <main id="main-content" class="app-container">
