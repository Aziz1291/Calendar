<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — Company Calendar</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='0.9em' font-size='90'>📅</text></svg>">
    <style>
        :root {
            --accent: #4f46e5;
            --font: 'Inter', system-ui, sans-serif;
        }
        * { box-sizing: border-box; }
        body {
            font-family: var(--font);
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4f46e5 100%);
            padding: 1rem;
        }
        .register-wrapper {
            width: 100%;
            max-width: 440px;
        }
        .brand-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .brand-header .brand-icon {
            font-size: 3rem;
            display: block;
            margin-bottom: 0.5rem;
        }
        .brand-header h1 {
            color: #fff;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }
        .brand-header p {
            color: rgba(255,255,255,0.6);
            font-size: 0.875rem;
            margin: 0.3rem 0 0;
        }
        .register-card {
            background: rgba(255,255,255,0.97);
            border-radius: 20px;
            padding: 2rem 2rem 1.75rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .register-card h2 {
            font-size: 1.2rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #374151;
            margin-bottom: 0.4rem;
        }
        .form-control {
            border-radius: 10px !important;
            border: 1.5px solid #e5e7eb;
            padding: 0.65rem 0.9rem;
            font-size: 0.9rem;
            font-family: var(--font);
            transition: all 0.2s;
        }
        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(79,70,229,0.12);
            outline: none;
        }
        .btn-register {
            background: var(--accent);
            border: none;
            color: #fff;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.95rem;
            padding: 0.7rem;
            width: 100%;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 0.25rem;
            box-shadow: 0 4px 14px rgba(79,70,229,0.35);
            font-family: var(--font);
        }
        .btn-register:hover {
            background: #4338ca;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(79,70,229,0.45);
        }
        .divider {
            text-align: center;
            margin: 1.25rem 0;
            color: #9ca3af;
            font-size: 0.85rem;
            position: relative;
        }
        .divider::before, .divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 42%;
            height: 1px;
            background: #e5e7eb;
        }
        .divider::before { left: 0; }
        .divider::after { right: 0; }
        .login-link {
            text-align: center;
            font-size: 0.875rem;
            color: #6b7280;
        }
        .login-link a {
            color: var(--accent);
            font-weight: 600;
            text-decoration: none;
        }
        .login-link a:hover { text-decoration: underline; }
        .alert-error {
            background: rgba(244,63,94,0.08);
            border: 1.5px solid rgba(244,63,94,0.2);
            color: #9f1239;
            border-radius: 10px;
            padding: 0.65rem 0.9rem;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .mb-3 { margin-bottom: 1rem !important; }
        .mb-4 { margin-bottom: 1.5rem !important; }
    </style>
</head>
<body>
    <div class="register-wrapper">
        <div class="brand-header">
            <span class="brand-icon">📅</span>
            <h1>Company Calendar</h1>
            <p>Create your team account</p>
        </div>
        <div class="register-card">
            <h2>Create account</h2>

            <?php if (isset($_GET['error'])): ?>
                <?php if ($_GET['error'] === 'empty_fields'): ?>
                <div class="alert-error"><span>⚠️</span> Please fill in all fields.</div>
                <?php elseif ($_GET['error'] === 'exists'): ?>
                <div class="alert-error"><span>⚠️</span> Username or email already taken.</div>
                <?php endif; ?>
            <?php endif; ?>

            <form action="registerAction.php" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username"
                        placeholder="Choose a username" required autocomplete="username">
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" class="form-control" id="email" name="email"
                        placeholder="name@company.com" required autocomplete="email">
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password"
                        placeholder="Create a strong password" required autocomplete="new-password">
                </div>
                <button type="submit" class="btn-register">Create account →</button>
            </form>

            <div class="divider">or</div>

            <div class="login-link">
                Already have an account? <a href="login.php">Sign in</a>
            </div>
        </div>
    </div>
</body>
</html>