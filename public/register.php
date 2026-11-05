<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — Calendar Manager</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/calendar.css?t=<?= time() ?>">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='0.9em' font-size='90'>📅</text></svg>">
</head>
<body class="auth-page">
    <div class="auth-wrapper">
        <div class="auth-brand">
            <span class="brand-icon">📅</span>
            <h1>Calendar Manager</h1>
            <p>Join Your Team Scheduling Workspace</p>
        </div>

        <div class="auth-card">
            <h2>Create Employee Account</h2>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger d-flex align-items-center gap-2">
                    <span>&bull;</span>
                    <span>
                        <?php if ($_GET['error'] === 'empty_fields'): ?>
                            Please fill in all required registration fields.
                        <?php elseif ($_GET['error'] === 'exists'): ?>
                            Username or email address is already registered in the system.
                        <?php else: ?>
                            An error occurred during account creation.
                        <?php endif; ?>
                    </span>
                </div>
            <?php endif; ?>

            <form action="registerAction.php" method="POST">
                <div class="form-group mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username"
                           placeholder="Choose an account handle" required autocomplete="username">
                </div>

                <div class="form-group mb-3">
                    <label for="email" class="form-label">Corporate Email Address</label>
                    <input type="email" class="form-control" id="email" name="email"
                           placeholder="employee@company.com" required autocomplete="email">
                </div>

                <div class="form-group mb-4">
                    <label for="password" class="form-label">Secure Password</label>
                    <input type="password" class="form-control" id="password" name="password"
                           placeholder="At least 6 characters" required autocomplete="new-password">
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    Register Account &rarr;
                </button>
            </form>

            <div class="auth-footer">
                Already registered? <a href="login.php" class="fw-semibold">Sign in here</a>
            </div>
        </div>

        <div class="text-center mt-3" style="font-size: 0.78rem; color: var(--muted-light);">
            &copy; <?= date('Y') ?> Calendar Manager &middot; Enterprise Leave Management
        </div>
    </div>
</body>
</html>
