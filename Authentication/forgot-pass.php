<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — YP4K Resort</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/resort-theme.css">
    <link rel="stylesheet" href="../css/auth-resort.css">
    <style>
        /* Step Indicator */
        .step-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            margin-bottom: 2rem;
        }
        .step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.4rem;
        }
        .step-num {
            width: 36px; height: 36px;
            border-radius: 50%;
            border: 2px solid var(--border);
            display: flex; align-items: center; justify-content: center;
            font-size: 0.8rem; font-weight: 700;
            color: var(--text-muted);
            background: rgba(8,18,14,0.5);
            transition: var(--t);
        }
        .step-item.active .step-num {
            border-color: var(--accent);
            color: var(--accent);
            background: rgba(157,255,0,0.08);
            box-shadow: 0 0 15px rgba(157,255,0,0.2);
        }
        .step-item.done .step-num {
            border-color: var(--success);
            background: var(--success);
            color: #000;
        }
        .step-label {
            font-size: 0.68rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--text-dim);
        }
        .step-item.active .step-label { color: var(--accent); }
        .step-item.done .step-label { color: var(--success); }
        .step-connector {
            width: 60px; height: 1px;
            background: var(--border);
            flex-shrink: 0;
            margin-bottom: 1rem;
        }
        .step-item.done + .step-connector { background: var(--success); }

        /* Step content */
        .step-content { display: none; }
        .step-content.active { display: block; animation: fadeSlide 0.4s ease; }
        @keyframes fadeSlide {
            from { opacity:0; transform: translateY(10px); }
            to   { opacity:1; transform: translateY(0); }
        }
    </style>
</head>
<body>
<div class="resort-bg"></div>
<div class="particles">
    <div class="particle"></div><div class="particle"></div>
    <div class="particle"></div><div class="particle"></div>
</div>

<div style="position:relative;z-index:10;width:100%;display:flex;justify-content:center;align-items:center;min-height:100vh;padding:2rem 1rem;">
    <div class="auth-card">
        <!-- Logo -->
        <div class="auth-logo">
            <div class="auth-logo-icon" style="background:linear-gradient(135deg,rgba(157,255,0,0.2),rgba(110,220,69,0.2));border:1px solid rgba(157,255,0,0.3);">
                <i class="bi bi-shield-lock" style="color:var(--accent);"></i>
            </div>
            <h4>Reset Password</h4>
            <p>Securely recover your account access</p>
        </div>

        <!-- Step Indicator -->
        <div class="step-indicator">
            <div class="step-item active" id="step1Indicator">
                <div class="step-num">1</div>
                <span class="step-label">Email</span>
            </div>
            <div class="step-connector"></div>
            <div class="step-item" id="step2Indicator">
                <div class="step-num">2</div>
                <span class="step-label">Verify</span>
            </div>
            <div class="step-connector"></div>
            <div class="step-item" id="step3Indicator">
                <div class="step-num">3</div>
                <span class="step-label">Reset</span>
            </div>
        </div>

        <!-- Alerts -->
        <div id="successAlert" class="auth-alert auth-alert-success" style="display:none;">
            <i class="bi bi-check-circle"></i><span id="successMessage"></span>
        </div>
        <div id="errorAlert" class="auth-alert auth-alert-error" style="display:none;">
            <i class="bi bi-exclamation-triangle"></i><span id="errorMessage"></span>
        </div>
        <div id="infoAlert" class="auth-alert" style="display:none;background:rgba(56,189,248,0.1);border:1px solid rgba(56,189,248,0.2);color:var(--info);">
            <i class="bi bi-info-circle"></i><span id="infoMessage"></span>
        </div>

        <!-- Step 1: Email -->
        <div class="step-content active" id="step1">
            <form id="emailForm">
                <div class="auth-field">
                    <label class="auth-label"><i class="bi bi-envelope me-1"></i>Email Address</label>
                    <input type="email" class="auth-input" id="email" name="email" placeholder="Enter your registered email" required>
                </div>
                <button type="submit" class="auth-submit" id="sendCodeBtn">
                    <i class="bi bi-send"></i>
                    <span>Send Reset Code</span>
                </button>
            </form>
        </div>

        <!-- Step 2: Verify -->
        <div class="step-content" id="step2">
            <form id="verifyForm">
                <div class="auth-field">
                    <label class="auth-label"><i class="bi bi-key me-1"></i>Verification Code</label>
                    <input type="text" class="auth-input" id="verificationCode" name="verificationCode"
                           placeholder="Enter 6-digit code" maxlength="6" required style="text-align:center;font-size:1.5rem;font-weight:700;letter-spacing:0.2em;">
                    <small style="color:var(--text-muted);font-size:0.78rem;display:block;margin-top:0.4rem;">Check your email for the verification code</small>
                </div>
                <button type="submit" class="auth-submit" id="verifyCodeBtn">
                    <i class="bi bi-check-circle"></i><span>Verify Code</span>
                </button>
                <button type="button" class="btn-resort-ghost w-100 justify-content-center mt-2" onclick="resendCode()">
                    <i class="bi bi-arrow-clockwise"></i> Resend Code
                </button>
            </form>
        </div>

        <!-- Step 3: New Password -->
        <div class="step-content" id="step3">
            <form id="resetForm">
                <div class="auth-field">
                    <label class="auth-label"><i class="bi bi-lock me-1"></i>New Password</label>
                    <div class="auth-input-group">
                        <input type="password" class="auth-input" id="newPassword" name="newPassword" placeholder="Create a strong password" required>
                        <button type="button" class="auth-eye-btn" onclick="togglePassword('newPassword', 'newPasswordEye')">
                            <i class="bi bi-eye" id="newPasswordEye"></i>
                        </button>
                    </div>
                    <small style="color:var(--text-muted);font-size:0.78rem;display:block;margin-top:0.4rem;">At least 8 characters long</small>
                </div>
                <div class="auth-field">
                    <label class="auth-label"><i class="bi bi-lock-fill me-1"></i>Confirm Password</label>
                    <div class="auth-input-group">
                        <input type="password" class="auth-input" id="confirmNewPassword" name="confirmNewPassword" placeholder="Confirm your new password" required>
                        <button type="button" class="auth-eye-btn" onclick="togglePassword('confirmNewPassword', 'confirmEye')">
                            <i class="bi bi-eye" id="confirmEye"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="auth-submit" id="resetPasswordBtn">
                    <i class="bi bi-shield-check"></i><span>Reset Password</span>
                </button>
            </form>
        </div>

        <div class="auth-divider"><span>OR</span></div>
        <p class="auth-text">Remember your password? <a href="login.php" class="auth-link">Sign In</a></p>
    </div>
</div>

<script>
    let currentStep = 1;

    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token');
        const email = urlParams.get('email');
        if (token && email) {
            localStorage.setItem('resetEmail', email);
            localStorage.setItem('resetToken', token);
            goToStep(3);
            showInfo('Token received. Please create your new password.');
        }
        if (urlParams.get('success')) showSuccess(decodeURIComponent(urlParams.get('success')));
        if (urlParams.get('error')) showError(decodeURIComponent(urlParams.get('error')));
    });

    document.getElementById('emailForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const email = document.getElementById('email').value;
        const btn = document.getElementById('sendCodeBtn');
        btn.disabled = true;
        btn.querySelector('span').textContent = 'Sending...';
        setTimeout(() => {
            btn.disabled = false;
            btn.querySelector('span').textContent = 'Send Reset Code';
            localStorage.setItem('resetEmail', email);
            goToStep(2);
            showSuccess(`Reset code sent to ${email}. Check your inbox.`);
        }, 2000);
    });

    document.getElementById('verifyForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const code = document.getElementById('verificationCode').value;
        const btn = document.getElementById('verifyCodeBtn');
        btn.disabled = true;
        btn.querySelector('span').textContent = 'Verifying...';
        setTimeout(() => {
            btn.disabled = false;
            btn.querySelector('span').textContent = 'Verify Code';
            if (code.length === 6) { goToStep(3); showSuccess('Code verified. Create your new password.'); }
            else showError('Invalid code. Please try again.');
        }, 1500);
    });

    document.getElementById('resetForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const pass = document.getElementById('newPassword').value;
        const confirm = document.getElementById('confirmNewPassword').value;
        if (pass.length < 8) { showError('Password must be at least 8 characters.'); return; }
        if (pass !== confirm) { showError('Passwords do not match.'); return; }
        const btn = document.getElementById('resetPasswordBtn');
        btn.disabled = true;
        btn.querySelector('span').textContent = 'Resetting...';
        setTimeout(() => {
            btn.disabled = false;
            btn.querySelector('span').textContent = 'Reset Password';
            localStorage.removeItem('resetEmail');
            localStorage.removeItem('resetToken');
            showSuccess('Password reset! Redirecting to login...');
            setTimeout(() => window.location.href = 'login.php?success=Password reset successfully', 2000);
        }, 2000);
    });

    function goToStep(step) {
        document.querySelectorAll('.step-content').forEach(c => c.classList.remove('active'));
        document.getElementById(`step${step}`).classList.add('active');
        for (let i = 1; i <= 3; i++) {
            const el = document.getElementById(`step${i}Indicator`);
            el.classList.remove('active', 'done');
            if (i < step) el.classList.add('done');
            else if (i === step) el.classList.add('active');
        }
        currentStep = step;
    }

    function resendCode() {
        const email = localStorage.getItem('resetEmail');
        if (email) showInfo(`Reset code resent to ${email}.`);
        else { showError('Please enter your email first.'); goToStep(1); }
    }

    function togglePassword(fieldId, iconId) {
        const f = document.getElementById(fieldId);
        const e = document.getElementById(iconId);
        if (f.type === 'password') { f.type = 'text'; e.classList.replace('bi-eye', 'bi-eye-slash'); }
        else { f.type = 'password'; e.classList.replace('bi-eye-slash', 'bi-eye'); }
    }

    function showSuccess(msg) { hideAlerts(); document.getElementById('successMessage').textContent = msg; document.getElementById('successAlert').style.display = 'flex'; setTimeout(() => document.getElementById('successAlert').style.display = 'none', 5000); }
    function showError(msg)   { hideAlerts(); document.getElementById('errorMessage').textContent = msg;   document.getElementById('errorAlert').style.display = 'flex';   setTimeout(() => document.getElementById('errorAlert').style.display = 'none', 5000); }
    function showInfo(msg)    { hideAlerts(); document.getElementById('infoMessage').textContent = msg;    document.getElementById('infoAlert').style.display = 'flex';    setTimeout(() => document.getElementById('infoAlert').style.display = 'none', 5000); }
    function hideAlerts()     { ['successAlert','errorAlert','infoAlert'].forEach(id => document.getElementById(id).style.display = 'none'); }

    document.addEventListener('keydown', e => {
        if (e.ctrlKey && e.shiftKey && (e.key === 'l' || e.key === 'L')) { e.preventDefault(); window.location.href = 'admin-login.php'; }
    });
</script>
</body>
</html>
