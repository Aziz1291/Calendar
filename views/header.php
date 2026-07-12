<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? h($title) : 'Company Calendar'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="css/calendar.css?t=<?= time() ?>">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='0.9em' font-size='90'>📅</text></svg>">
</head>

<body>
    <nav class="navbar navbar-expand-lg mb-3">
        <div class="container-fluid">
            <div class="d-flex align-items-center gap-1">
                <a href="index.php" class="navbar-brand">📅 Calendar</a>
                <?php if (is_authenticated()): ?>
                    <?php $navUser = current_user(); ?>
                    <a href="dashboard.php" class="navbar-brand">Dashboard</a>
                    <?php if ($navUser->isAdmin()): ?>
                        <a href="eventsTable.php" class="navbar-brand">All Requests</a>
                        <a href="usersTable.php" class="navbar-brand">Employees</a>
                    <?php else: ?>
                        <a href="eventsTable.php" class="navbar-brand">My Requests</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <div class="d-flex align-items-center gap-2">
                <?php if (is_authenticated()): ?>
                    <?php $user = current_user(); ?>
                    <span class="navbar-text text-light me-1" style="font-size:0.85rem;">
                        👋 <strong><?= h($user->getUsername()) ?></strong>
                        <span class="badge ms-1" style="background:rgba(255,255,255,0.15);color:#c7d2fe;font-size:0.7rem;">
                            <?= h($user->getRole()) ?>
                        </span>
                    </span>
                    <a href="logout.php" class="btn btn-sm" style="background:rgba(255,255,255,0.1);color:#e0e7ff;border:1px solid rgba(255,255,255,0.15);">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-sm" style="background:rgba(255,255,255,0.1);color:#e0e7ff;border:1px solid rgba(255,255,255,0.2);">Login</a>
                    <a href="register.php" class="btn btn-sm btn-primary">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>