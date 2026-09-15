<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login — YP4K Resort</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/resort-theme.css">
    <link rel="stylesheet" href="../css/auth-resort.css">
    <style>
        .staff-badge {
            display: inline-flex; align-items: center; gap: 0.4rem;
            background: rgba(99,102,241,0.1); border: 1px solid rgba(99,102,241,0.35);
            color: #818cf8; font-size: 0.68rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.1em;
            padding: 0.25rem 0.75rem; border-radius: 100px;
            margin-bottom: 1rem;
        }
        .auth-card::before {
            background: linear-gradient(90deg, transparent, #818cf8, transparent);
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
        <div class="auth-logo">
            <div class="auth-logo-icon"><i class="bi bi-person-badge"></i></div>
            <div class="staff-badge"><i class="bi bi-person-check"></i> Staff Access</div>
            <h4>YP4K Resort</h4>
            <p>Staff Portal — Authorized Personnel Only</p>
        </div>

        <div id="errorMessage" class="auth-alert auth-alert-error" style="display:none;">
            <i class="bi bi-exclamation-circle"></i>
            <span id="errorText"></span>
        </div>

        <form action="../php/staff_login_process.php" method="POST" id="staffLoginForm">
            <div class="auth-field">
                <label for="email" class="auth-label"><i class="bi bi-envelope me-1"></i>Staff Email</label>
                <input class="auth-input" type="email" id="email" name="email" placeholder="Enter staff email" required autocomplete="email">
            </div>
            <div class="auth-field">
                <label for="password" class="auth-label"><i class="bi bi-lock me-1"></i>Password</label>
                <div class="auth-input-group">
                    <input class="auth-input" type="password" id="password" name="password" placeholder="Enter password" required autocomplete="current-password">
                    <button type="button" class="auth-eye-btn" onclick="togglePasswordVisibility()">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="auth-submit">
                <i class="bi bi-box-arrow-in-right"></i> Sign In
            </button>
        </form>
        <p style="text-align:center;margin-top:1rem;font-size:0.82rem;color:var(--text-muted);">
            Admin? <a href="admin-login.php" class="auth-link">Admin Login →</a>
        </p>
    </div>
</div>

<script>
function togglePasswordVisibility() {
    const pw = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');
    if (pw.type === 'password') { pw.type = 'text'; icon.className = 'bi bi-eye-slash'; }
    else { pw.type = 'password'; icon.className = 'bi bi-eye'; }
}
const urlParams = new URLSearchParams(window.location.search);
const err = urlParams.get('error');
if (err) {
    document.getElementById('errorMessage').style.display = 'flex';
    document.getElementById('errorText').textContent = err;
}
</script>
</body>
</html>
