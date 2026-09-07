<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — YP4K Resort</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/resort-theme.css">
    <link rel="stylesheet" href="../css/auth-resort.css">
    <style>
        /* Admin variant — slightly cooler/corporate tone */
        :root {
            --accent: #9DFF00;
            --accent-2: #6EDC45;
        }
        .admin-badge {
            display: inline-flex; align-items: center; gap: 0.4rem;
            background: rgba(157,255,0,0.08); border: 1px solid rgba(157,255,0,0.2);
            color: var(--accent-2); font-size: 0.68rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.1em;
            padding: 0.25rem 0.75rem; border-radius: 100px;
            margin-bottom: 1rem;
        }
        .auth-card::before {
            background: linear-gradient(90deg, transparent, var(--accent), transparent);
        }
    </style>
</head>
<body>
<div class="resort-bg"></div>
<div class="particles">
    <div class="particle"></div><div class="particle"></div>
    <div class="particle"></div><div class="particle"></div>
    <div class="particle"></div><div class="particle"></div>
</div>

<div style="position:relative;z-index:10;width:100%;display:flex;justify-content:center;align-items:center;min-height:100vh;padding:2rem 1rem;">
    <div class="auth-card">
        <!-- Logo -->
        <div class="auth-logo">
            <div class="auth-logo-icon"><i class="bi bi-shield-lock"></i></div>
            <div class="admin-badge"><i class="bi bi-shield-check"></i> Administrator</div>
            <h4>YP4K Resort</h4>
            <p>Admin Portal — Authorized Access Only</p>
        </div>

        <!-- Error Message from server -->
        <div id="errorMessage" class="auth-alert auth-alert-error" style="display:none;">
            <i class="bi bi-exclamation-circle"></i>
            <span id="errorText"></span>
        </div>

        <!-- Admin Login Form — backend action unchanged -->
        <form action="../php/admin_login_process.php" method="POST" id="adminLoginForm">
            <div class="auth-field">
                <label for="email" class="auth-label"><i class="bi bi-envelope me-1"></i>Admin Email</label>
                <input class="auth-input" type="email" id="email" name="email" placeholder="Enter admin email" required autocomplete="email">
            </div>

            <div class="auth-field">
                <label for="password" class="auth-label"><i class="bi bi-lock me-1"></i>Admin Password</label>
                <div class="auth-input-group">
                    <input class="auth-input" type="password" id="password" name="password" placeholder="Enter admin password" required autocomplete="current-password">
                    <button type="button" class="auth-eye-btn" onclick="togglePasswordVisibility()" id="togglePassword">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4" style="font-size:0.85rem;">
                <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;color:var(--text-muted);">
                    <input class="auth-check" type="checkbox" id="rememberMe" name="remember">
                    Remember me
                </label>
                <a href="forgot-pass.php" class="auth-link">Forgot Password?</a>
            </div>

            <button type="submit" class="auth-submit">
                <i class="bi bi-shield-check"></i>
                Admin Sign In
            </button>
        </form>

        <div class="auth-divider"><span>OR</span></div>

        <p class="auth-text">
            <i class="bi bi-person me-1" style="color:var(--accent-2);"></i>
            User account? <a href="login.php" class="auth-link">Sign in as User</a>
        </p>

    </div>
</div>

<script>
    function togglePasswordVisibility() {
        const passwordField = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            eyeIcon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            passwordField.type = 'password';
            eyeIcon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    }

    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
    if (error) {
        const el = document.getElementById('errorMessage');
        document.getElementById('errorText').textContent = decodeURIComponent(error);
        el.style.display = 'flex';
    }
</script>
</body>
</html>
