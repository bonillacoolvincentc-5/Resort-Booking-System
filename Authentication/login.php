<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — YP4K Resort</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/resort-theme.css">
    <link rel="stylesheet" href="../css/auth-resort.css">
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
            <div class="auth-logo-icon"><i class="bi bi-gem"></i></div>
            <h4>YP4K Resort</h4>
            <p>Welcome back! Please sign in to continue.</p>
        </div>

        <!-- Error Message from server -->
        <div id="errorMessage" class="auth-alert auth-alert-error" style="display:none;">
            <i class="bi bi-exclamation-circle"></i>
            <span id="errorText"></span>
        </div>

        <!-- Login Form — backend unchanged -->
        <form action="../phpAuthentication/login_process.php" method="POST" id="loginForm">
            <div class="auth-field">
                <label for="email" class="auth-label">Email Address</label>
                <input class="auth-input" type="email" id="email" name="email" placeholder="you@example.com" required autocomplete="email">
            </div>

            <div class="auth-field">
                <label for="password" class="auth-label">Password</label>
                <div class="auth-input-group">
                    <input class="auth-input" type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
                    <button type="button" class="auth-eye-btn" onclick="togglePasswordVisibility()" id="togglePassword" aria-label="Toggle password visibility">
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
                <i class="bi bi-box-arrow-in-right"></i>
                Sign In
            </button>
        </form>

        <div class="auth-divider"><span>OR</span></div>

        <p class="auth-text">
            Don't have an account?
            <a href="register.php" class="auth-link">Create one here</a>
        </p>

        <div class="text-center mt-3">
            <a href="../index-new.html" class="auth-back">
                <i class="bi bi-arrow-left"></i> Back to Home
            </a>
        </div>
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

    // Display error messages from server
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
    if (error) {
        const el = document.getElementById('errorMessage');
        document.getElementById('errorText').textContent = decodeURIComponent(error);
        el.style.display = 'flex';
    }

    // Global keyboard shortcut for admin access
    document.addEventListener('keydown', function(event) {
        if (event.ctrlKey && event.shiftKey && (event.key === 'l' || event.key === 'L')) {
            event.preventDefault();
            window.location.href = 'admin-login.php';
        }
    });
</script>
</body>
</html>
