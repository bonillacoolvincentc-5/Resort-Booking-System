<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - YP4K Resort</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/verify.css">
</head>
<body>
    <!-- Background Animation -->
    <div class="bg-animation">
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        <div class="floating-shape shape-3"></div>
    </div>

    <!-- Main Container -->
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="col-md-5">
            <div class="card shadow-lg border-0" style="border-radius: 15px;">
                <div class="card-body p-5" style="background: linear-gradient(135deg, var(--bg-card) 0%, var(--bg-tertiary) 100%);">
                    <!-- Logo Section -->
                    <div class="text-center mb-4">
                        <i class="bi bi-envelope-check" style="font-size: 3rem; color: var(--accent-primary);"></i>
                        <h3 class="mt-2 fw-bold" style="color: var(--text-primary);">Email Verification</h3>
                        <p class="text-muted">Enter the 6-digit code sent to your email</p>
                    </div>

                    <!-- Email Display -->
                    <div class="mb-4 text-center">
                        <p class="mb-0" style="color: var(--text-secondary);">
                            <i class="bi bi-envelope me-2"></i>
                            <span id="userEmail">loading...</span>
                        </p>
                    </div>

                    <form id="verificationForm" method="POST">
                        <!-- OTP Input -->
                        <div class="mb-4">
                            <label for="otpCode" class="form-label fw-bold" style="color: var(--text-primary);">
                                <i class="bi bi-shield-check me-2" style="color: var(--accent-primary);"></i>Verification Code
                            </label>
                            <div class="otp-input-container">
                                <input type="text" class="form-control form-control-lg text-center" id="otpCode" name="otpCode" 
                                       placeholder="000000" maxlength="6" required autocomplete="off"
                                       style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary); font-size: 1.5rem; letter-spacing: 0.5rem;">
                            </div>
                            <small class="text-muted d-block mt-2" style="color: var(--text-primary);">
                                Enter the 6-digit code from your email
                            </small>
                        </div>

                        <!-- Verify Button -->
                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold mb-3" id="verifyBtn"
                                style="background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary)); border: none;">
                            <i class="bi bi-check-circle me-2"></i>Verify Email
                        </button>

                        <!-- Resend Section -->
                        <div class="text-center">
                            <p class="mb-2" style="color: var(--text-secondary);">
                                Didn't receive the code?
                            </p>
                            <button type="button" class="btn btn-outline-secondary" id="resendBtn" disabled
                                    style="background: transparent; border: 1px solid var(--border-color); color: var(--text-secondary);">
                                <i class="bi bi-arrow-clockwise me-2"></i>
                                <span id="resendText">Resend Code</span>
                            </button>
                            <div id="cooldownTimer" class="mt-2" style="color: var(--text-muted); font-size: 0.875rem; display: none;"></div>
                        </div>
                    </form>

                    <!-- Messages -->
                    <div id="errorMessage" class="alert alert-danger d-none mt-3" role="alert"
                         style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: var(--accent-danger);">
                    </div>
                    <div id="successMessage" class="alert alert-success d-none mt-3" role="alert"
                         style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: var(--accent-success);">
                    </div>

                    <!-- Back to Registration -->
                    <div class="text-center mt-4">
                        <a href="javascript:history.back()" class="text-decoration-none" style="color: var(--accent-primary);">
                            <i class="bi bi-arrow-left me-1"></i>Back to Registration
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let cooldownInterval;
        let verificationData;

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadVerificationData();
            setupOTPInput();
            setupFormHandlers();
            startCooldownTimer();
        });

        function loadVerificationData() {
            // Get verification data from session storage or URL params
            const urlParams = new URLSearchParams(window.location.search);
            const email = urlParams.get('email');
            
            if (email) {
                document.getElementById('userEmail').textContent = email;
            } else {
                // Try to get from session
                fetch('../php/get_verification_data.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.email) {
                            document.getElementById('userEmail').textContent = data.email;
                        } else {
                            showError('Verification session expired. Please register again.');
                            setTimeout(() => {
                                window.location.href = 'register.php';
                            }, 3000);
                        }
                    })
                    .catch(error => {
                        console.error('Error loading verification data:', error);
                        showError('Unable to load verification data.');
                    });
            }
        }

        function setupOTPInput() {
            const otpInput = document.getElementById('otpCode');
            
            // Only allow numbers
            otpInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
            
            // Auto-focus next input (if using multiple inputs)
            otpInput.addEventListener('keyup', function(e) {
                if (this.value.length === 6) {
                    document.getElementById('verifyBtn').focus();
                }
            });
        }

        function setupFormHandlers() {
            // Verification form submission
            document.getElementById('verificationForm').addEventListener('submit', function(e) {
                e.preventDefault();
                verifyOTP();
            });

            // Resend button
            document.getElementById('resendBtn').addEventListener('click', function() {
                resendOTP();
            });
        }

        function verifyOTP() {
            const otpCode = document.getElementById('otpCode').value;
            const verifyBtn = document.getElementById('verifyBtn');
            const email = getCurrentEmail();
            
            if (otpCode.length !== 6) {
                showError('Please enter a 6-digit verification code');
                return;
            }

            // Show loading state
            verifyBtn.disabled = true;
            verifyBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Verifying...';

            fetch('../php/verify_otp.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `otp=${encodeURIComponent(otpCode)}&email=${encodeURIComponent(email)}`
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(text => {
                try {
                    return JSON.parse(text);
                } catch (parseError) {
                    throw new Error('Invalid JSON response');
                }
            })
            .then(data => {
                if (data.success) {
                    showSuccess(data.message);
                    setTimeout(() => {
                        window.location.href = data.redirect || 'login.php';
                    }, 2000);
                } else {
                    if (data.error_type === 'invalid_otp') {
                        showError('Invalid or incorrect verification code.');
                    } else if (data.message) {
                        showError(data.message);
                    } else {
                        showError('Something went wrong. Please try again.');
                    }
                    document.getElementById('otpCode').value = '';
                    document.getElementById('otpCode').focus();
                }
            })
            .catch(error => {
                console.error('Verification error:', error);
                showError('Something went wrong. Please try again.');
            })
            .finally(() => {
                verifyBtn.disabled = false;
                verifyBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Verify Email';
            });
        }

        function resendOTP() {
            const resendBtn = document.getElementById('resendBtn');
            const resendText = document.getElementById('resendText');
            const userEmail = document.getElementById('userEmail').textContent;
            
            // Show loading state
            resendBtn.disabled = true;
            resendText.textContent = 'Sending...';

            fetch('../php/resend_otp.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `email=${encodeURIComponent(userEmail)}`
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showSuccess(data.message);
                    startCooldownTimer();
                    document.getElementById('otpCode').value = '';
                    document.getElementById('otpCode').focus();
                } else {
                    showError(data.message || 'Something went wrong. Please try again.');
                    const remainingSeconds = Number(data.cooldown || 0);
                    if (remainingSeconds > 0) {
                        updateCooldownButton(remainingSeconds);
                    } else {
                        enableResendButton();
                    }
                }
            })
            .catch(error => {
                console.error('Resend error:', error);
                showError('Something went wrong. Please try again.');
                enableResendButton();
            })
            .finally(() => {
                if (!document.getElementById('resendBtn').disabled) {
                    resendText.textContent = 'Resend Code';
                }
            });
        }

        function startCooldownTimer() {
            const resendBtn = document.getElementById('resendBtn');
            const resendText = document.getElementById('resendText');
            const cooldownTimer = document.getElementById('cooldownTimer');
            
            // Check current cooldown status
            fetch('../php/get_cooldown_status.php')
                .then(response => response.json())
                .then(data => {
                    if (data.cooldown > 0) {
                        updateCooldownButton(data.cooldown);
                    } else {
                        enableResendButton();
                    }
                })
                .catch(error => {
                    console.error('Error checking cooldown:', error);
                    enableResendButton(); // Enable by default if error
                });
        }

        function updateCooldownButton(seconds) {
            const resendBtn = document.getElementById('resendBtn');
            const resendText = document.getElementById('resendText');
            const cooldownTimer = document.getElementById('cooldownTimer');
            
            resendBtn.disabled = true;
            resendText.style.display = 'inline';
            resendText.textContent = 'Resend Code';
            cooldownTimer.style.display = 'block';
            cooldownTimer.textContent = `Resend available in ${seconds}s`;
            
            if (window.cooldownInterval) {
                clearInterval(window.cooldownInterval);
            }
            
            window.cooldownInterval = setInterval(() => {
                seconds--;
                
                if (seconds <= 0) {
                    clearInterval(window.cooldownInterval);
                    enableResendButton();
                } else {
                    cooldownTimer.textContent = `Resend available in ${seconds}s`;
                }
            }, 1000);
        }

        function enableResendButton() {
            const resendBtn = document.getElementById('resendBtn');
            const resendText = document.getElementById('resendText');
            const cooldownTimer = document.getElementById('cooldownTimer');
            
            resendBtn.disabled = false;
            resendText.style.display = 'inline';
            resendText.textContent = 'Resend Code';
            cooldownTimer.style.display = 'none';
            cooldownTimer.textContent = '';
            
            if (window.cooldownInterval) {
                clearInterval(window.cooldownInterval);
            }
        }

        function getCurrentEmail() {
            const emailText = document.getElementById('userEmail').textContent || '';
            if (emailText && emailText !== 'loading...') {
                return emailText.trim();
            }
            return '';
        }

        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.textContent = message;
            errorDiv.classList.remove('d-none');
            
            // Hide success message if showing
            document.getElementById('successMessage').classList.add('d-none');
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                errorDiv.classList.add('d-none');
            }, 5000);
        }

        function showSuccess(message) {
            const successDiv = document.getElementById('successMessage');
            successDiv.textContent = message;
            successDiv.classList.remove('d-none');
            
            // Hide error message if showing
            document.getElementById('errorMessage').classList.add('d-none');
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                successDiv.classList.add('d-none');
            }, 5000);
        }
    </script>
</body>
</html>
