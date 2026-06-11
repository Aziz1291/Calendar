<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title><?php echo isset($title) ? h($title) : 'Company Calendar'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="css/calendar.css?t=<?= time() ?>">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='0.9em' font-size='90'>📅</text></svg>">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-3">
        <div class="container-fluid">
            <div>
                <a href="index.php" class="navbar-brand">Company Calendar</a>
                <?php if (is_authenticated()): ?>
                    <?php $navUser = current_user(); ?>
                    <a href="dashboard.php" class="navbar-brand">Dashboard</a>
                    <?php if ($navUser->isAdmin()): ?>
                        <a href="eventsTable.php" class="navbar-brand">All Requests</a>
                        <a href="usersTable.php" class="navbar-brand">Employees</a>
                    <?php else: ?>
                        <a href="eventsTable.php" class="navbar-brand">My Day-Off Requests</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <div class="d-flex">
                <?php if (is_authenticated()): ?>
                    <?php $user = current_user(); ?>
                    <span class="navbar-text me-3 text-light">
                        Hello, <?= h($user->getUsername()) ?>
                        <span class="badge bg-secondary ms-1"><?= h($user->getRole()) ?></span>
                    </span>
                    <a href="logout.php" class="btn btn-light btn-sm">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-light btn-sm me-2">Login</a>
                    <a href="register.php" class="btn btn-light btn-sm">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>