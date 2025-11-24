<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MatraC</title>
    <link rel="stylesheet" href="<?= asset('css/reset.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/layout.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/forms.css') ?>">
    <style>
        .login-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: var(--bg-secondary);
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            background-color: var(--bg-primary);
            padding: 2rem;
            border-radius: 8px;
            box-shadow: var(--shadow-lg);
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-logo {
            font-size: 2rem;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            color: var(--text-secondary);
            font-size: 0.938rem;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">MatraC</div>
                <div class="login-subtitle">Material Traceability System</div>
            </div>

            <?php if ($error = flash('error')): ?>
                <div class="alert alert--error" style="margin-bottom: 1.5rem;">
                    <div class="alert__message"><?= h($error) ?></div>
                </div>
            <?php endif; ?>

            <div class="alert alert--info" style="margin-bottom: 1.5rem;">
                <div class="alert__title">Please Log In</div>
                <!-- <div class="alert__message">
                    <strong>Username:</strong> admin, receptor, issuer, or mixer<br>
                    <strong>Password:</strong> admin123 (for admin) or test123 (others)
                </div> -->
            </div>

            <form method="POST" action="<?= url('/login') ?>">
                <input type="hidden" name="csrf_token" value="<?= h(generateCsrfToken()) ?>">

                <div class="form-group">
                    <label for="username" class="form-label form-label--required">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="form-input"
                        required
                        autocomplete="username"
                        autofocus>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label form-label--required">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input"
                        required
                        autocomplete="current-password">
                </div>

                <button type="submit" class="btn btn--primary btn--block" style="margin-top: 1.5rem;">
                    Login
                </button>
            </form>

            <div style="text-align: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-light);">
                <span style="color: var(--text-muted); font-size: 0.938rem;">Version 1.0.0-dev</span>
            </div>
        </div>
    </div>
</body>

</html>