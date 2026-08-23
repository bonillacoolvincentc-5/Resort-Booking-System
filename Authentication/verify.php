<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification — YP4K Resort</title>
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
</div>

<div style="position:relative;z-index:10;width:100%;display:flex;justify-content:center;align-items:center;min-height:100vh;padding:2rem 1rem;">
    <div class="auth-card">
        <!-- Logo -->
        <div class="auth-logo">
            <div class="auth-logo-icon"><i class="bi bi-envelope-check"></i></div>
            <h4>Email Verification</h4>
            <p>Enter the 6-digit code sent to your email</p>
        </div>

        <!-- Email display -->
        <div style="text-align:center;margin-bottom:1.5rem;padding:0.75rem;background:rgba(157,255,0,0.05);border:1px solid rgba(157,255,0,0.12);border-radius:var(--radius-sm);">
            <i class="bi bi-envelope" style="color:var(--accent-2);margin-right:0.4rem;"></i>
            <span id="userEmail" style="color:var(--text-2);font-size:0.9rem;">loading...</span>
        </div>

        <!-- Alerts -->
        <div id="errorMessage" class="auth-alert auth-alert-error" style="display:none;">
            <i class="bi bi-exclamation-circle"></i><span id="errorText"></span>
        </div>
        <div id="successMessage" class="auth-alert auth-alert-success" style="display:none;">
            <i class="bi bi-check-circle"></i><span id="successText"></span>
        </div>

        <!-- Verification Form — backend action and field names preserved -->
        <form id="verificationForm" method="POST">
            <div class="auth-field">
                <label for="otpCode" class="auth-label"><i class="bi bi-shield-check me-1"></i>Verification Code</label>
                <input type="text" class="auth-input" id="otpCode" name="otpCode"
                       placeholder="000000" maxlength="6" required autocomplete="off"
                       style="text-align:center;font-size:1.8rem;font-weight:800;letter-spacing:0.3em;">
                <small style="color:var(--text-muted);font-size:0.78rem;display:block;margin-top:0.4rem;text-align:center;">
                    Enter the 6-digit code from your email
                </small>
            </div>

            <button type="submit" class="auth-submit" id="verifyBtn">
                <i class="bi bi-check-circle"></i>
                <span>Verify Email</span>
            </button>

            <!-- Resend -->
            <div style="text-align:center;margin-top:1.25rem;">
                <p style="color:var(--text-muted);font-size:0.85rem;margin-bottom:0.6rem;">Didn't receive the code?</p>
                <button type="button" class="btn-resort-ghost" id="resendBtn" disabled style="padding:0.5rem 1.25rem;font-size:0.85rem;">
                    <i class="bi bi-arrow-clockwise"></i>
                    <span id="resendText">Resend Code</span>
                </button>
                <div id="cooldownTimer" style="color:var(--text-muted);font-size:0.8rem;margin-top:0.5rem;display:none;"></div>
            </div>
        </form>

        <div class="auth-divider"><span>OR</span></div>
        <div class="text-center">
            <a href="javascript:history.back()" class="auth-back"><i class="bi bi-arrow-left"></i> Back to Registration</a>
        </div>
    </div>
</div>

<script>
    // ── All backend-calling JS preserved exactly ──
    let cooldownInterval;

    document.addEventListener('DOMContentLoaded', function() {
        loadVerificationData();
        setupOTPInput();
        setupFormHandlers();
        startCooldownTimer();
    });

    function loadVerificationData() {
        const urlParams = new URLSearchParams(window.location.search);
        const email = urlParams.get('email');
        if (email) {
            document.getElementById('userEmail').textContent = email;
        } else {
            fetch('../php/get_verification_data.php')
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.email) {
                        document.getElementById('userEmail').textContent = data.email;
                    } else {
                        showError('Verification session expired. Please register again.');
                        setTimeout(() => window.location.href = 'register.php', 3000);
                    }
                })
                .catch(() => showError('Unable to load verification data.'));
        }
    }

    function setupOTPInput() {
        const otpInput = document.getElementById('otpCode');
        otpInput.addEventListener('input', function() { this.value = this.value.replace(/[^0-9]/g, ''); });
        otpInput.addEventListener('keyup', function() {
            if (this.value.length === 6) document.getElementById('verifyBtn').focus();
        });
    }

    function setupFormHandlers() {
        document.getElementById('verificationForm').addEventListener('submit', function(e) { e.preventDefault(); verifyOTP(); });
        document.getElementById('resendBtn').addEventListener('click', resendOTP);
    }

    function verifyOTP() {
        const otpCode = document.getElementById('otpCode').value;
        const verifyBtn = document.getElementById('verifyBtn');
        const email = getCurrentEmail();
        if (otpCode.length !== 6) { showError('Please enter a 6-digit verification code'); return; }
        verifyBtn.disabled = true;
        verifyBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Verifying...';
        fetch('../php/verify_otp.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `otp=${encodeURIComponent(otpCode)}&email=${encodeURIComponent(email)}`
        })
        .then(r => { if (!r.ok) throw new Error('Network error'); return r.text(); })
        .then(text => { try { return JSON.parse(text); } catch { throw new Error('Invalid JSON'); } })
        .then(data => {
            if (data.success) { showSuccess(data.message); setTimeout(() => window.location.href = data.redirect || 'login.php', 2000); }
            else { showError(data.error_type === 'invalid_otp' ? 'Invalid or incorrect verification code.' : (data.message || 'Something went wrong.')); document.getElementById('otpCode').value = ''; document.getElementById('otpCode').focus(); }
        })
        .catch(() => showError('Something went wrong. Please try again.'))
        .finally(() => { verifyBtn.disabled = false; verifyBtn.innerHTML = '<i class="bi bi-check-circle"></i> <span>Verify Email</span>'; });
    }

    function resendOTP() {
        const resendBtn = document.getElementById('resendBtn');
        const resendText = document.getElementById('resendText');
        const userEmail = document.getElementById('userEmail').textContent;
        resendBtn.disabled = true;
        resendText.textContent = 'Sending...';
        fetch('../php/resend_otp.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `email=${encodeURIComponent(userEmail)}`
        })
        .then(r => { if (!r.ok) throw new Error('Network error'); return r.json(); })
        .then(data => {
            if (data.success) { showSuccess(data.message); startCooldownTimer(); document.getElementById('otpCode').value = ''; document.getElementById('otpCode').focus(); }
            else {
                showError(data.message || 'Something went wrong.');
                const rem = Number(data.cooldown || 0);
                if (rem > 0) updateCooldownButton(rem); else enableResendButton();
            }
        })
        .catch(() => { showError('Something went wrong.'); enableResendButton(); })
        .finally(() => { if (!resendBtn.disabled) resendText.textContent = 'Resend Code'; });
    }

    function startCooldownTimer() {
        fetch('../php/get_cooldown_status.php').then(r => r.json())
        .then(data => { if (data.cooldown > 0) updateCooldownButton(data.cooldown); else enableResendButton(); })
        .catch(() => enableResendButton());
    }

    function updateCooldownButton(seconds) {
        const resendBtn = document.getElementById('resendBtn');
        const cooldownTimer = document.getElementById('cooldownTimer');
        resendBtn.disabled = true;
        cooldownTimer.style.display = 'block';
        cooldownTimer.textContent = `Resend available in ${seconds}s`;
        if (window.cooldownInterval) clearInterval(window.cooldownInterval);
        window.cooldownInterval = setInterval(() => {
            seconds--;
            if (seconds <= 0) { clearInterval(window.cooldownInterval); enableResendButton(); }
            else cooldownTimer.textContent = `Resend available in ${seconds}s`;
        }, 1000);
    }

    function enableResendButton() {
        const resendBtn = document.getElementById('resendBtn');
        const cooldownTimer = document.getElementById('cooldownTimer');
        resendBtn.disabled = false;
        document.getElementById('resendText').textContent = 'Resend Code';
        cooldownTimer.style.display = 'none';
        if (window.cooldownInterval) clearInterval(window.cooldownInterval);
    }

    function getCurrentEmail() {
        const t = document.getElementById('userEmail').textContent || '';
        return (t && t !== 'loading...') ? t.trim() : '';
    }

    function showError(msg)   { document.getElementById('errorText').textContent = msg;   const el = document.getElementById('errorMessage');   el.style.display = 'flex'; document.getElementById('successMessage').style.display = 'none'; setTimeout(() => el.style.display = 'none', 5000); }
    function showSuccess(msg) { document.getElementById('successText').textContent = msg; const el = document.getElementById('successMessage'); el.style.display = 'flex'; document.getElementById('errorMessage').style.display = 'none';   setTimeout(() => el.style.display = 'none', 5000); }
</script>
</body>
</html>
