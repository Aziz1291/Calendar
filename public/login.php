<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Calendar Manager demo for scheduling and reviewing time-off requests.">
    <title>Sign In — Calendar Manager</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/calendar.css?v=<?= filemtime(__DIR__ . '/css/calendar.css') ?>">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='0.9em' font-size='90'>📅</text></svg>">
</head>
<body class="auth-page">
    <div class="auth-wrapper">
        <div class="auth-brand">
            <span class="brand-icon">📅</span>
            <h1>Calendar Manager</h1>
            <p>Editorial Team Scheduling &amp; Leave Suite</p>
        </div>

        <div class="auth-card">
            <h2>Sign In to Workspace</h2>

            <?php if (isset($_GET['error']) && $_GET['error'] === '1'): ?>
                <div class="alert alert-danger d-flex align-items-center gap-2">
                    <span>&bull;</span>
                    <span>Invalid credentials. Please verify your details.</span>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success']) && $_GET['success'] === 'registered'): ?>
                <div class="alert alert-success d-flex align-items-center gap-2">
                    <span>&check;</span>
                    <span>Account created successfully! Please sign in.</span>
                </div>
            <?php endif; ?>

            <form action="loginAction.php" method="POST">
                <div class="form-group mb-3">
                    <label for="loginEmail" class="form-label">Email or Username</label>
                    <input type="text" class="form-control" id="loginEmail" name="email"
                           placeholder="name@company.com or username" required autocomplete="username">
                </div>

                <div class="form-group mb-4">
                    <label for="loginPassword" class="form-label">Password</label>
                    <input type="password" class="form-control" id="loginPassword" name="password"
                           placeholder="Enter account password" required autocomplete="current-password">
                </div>

                <button type="submit" class="btn btn-primary auth-card btn-submit w-100">
                    Sign In to Schedule &rarr;
                </button>

                <!-- DEMO CREDENTIALS -->
                <div class="demo-accounts">
                    <div class="demo-accounts__header">
                        <span class="demo-accounts__kicker">Quick Demo Accounts</span>
                        <span style="font-size:0.75rem; color:var(--muted);">Click to fill</span>
                    </div>
                    <div class="demo-accounts__grid">
                        <div class="demo-account-pill">
                            <div class="demo-account-pill__info">
                                <span class="demo-account-pill__role role-admin">&#x1F6E1;&#xFE0F; HR Admin</span>
                                <span class="demo-account-pill__name">Admin Lead (<code>admin1@company.com</code>)</span>
                            </div>
                            <button type="button" class="demo-account-pill__btn" onclick="fillCalendarDemo('admin1@company.com', 'password')">Use</button>
                        </div>
                        <div class="demo-account-pill">
                            <div class="demo-account-pill__info">
                                <span class="demo-account-pill__role role-user">&#x1F464; Employee</span>
                                <span class="demo-account-pill__name">John Employee (<code>john@company.com</code>)</span>
                            </div>
                            <button type="button" class="demo-account-pill__btn" onclick="fillCalendarDemo('john@company.com', 'password')">Use</button>
                        </div>
                    </div>
                </div>
            </form>

            <div class="auth-footer">
                Don't have an employee account? <a href="register.php" class="fw-semibold">Create an account</a>
            </div>
        </div>

        <div class="text-center mt-3" style="font-size: 0.78rem; color: var(--muted-light);">
            &copy; <?= date('Y') ?> Calendar Manager &middot; Enterprise Leave Management &middot; <a href="privacy.php">Privacy and cookies</a>
        </div>
    </div>

    <script>
        function fillCalendarDemo(email, password) {
            var emailField = document.getElementById('loginEmail');
            var passField = document.getElementById('loginPassword');
            if (emailField && passField) {
                emailField.value = email;
                passField.value = password;
                emailField.dispatchEvent(new Event('input', { bubbles: true }));
                passField.dispatchEvent(new Event('input', { bubbles: true }));
                passField.focus();
            }
        }
    </script>
</body>
</html>
