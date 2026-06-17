<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - YP4K Resort</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/forgotpass.css">
</head>
<body>
    <!-- Background Animation -->
    <div class="bg-animation">
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        <div class="floating-shape shape-3"></div>
    </div>

    <!-- Main Container -->
    <div class="auth-container">
        <div class="auth-card">
            <!-- Header -->
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="bi bi-shield-lock"></i>
                </div>
                <h1 class="auth-title">Reset Password</h1>
                <p class="auth-subtitle">Securely recover your account access</p>
            </div>

            <!-- Form Body -->
            <div class="auth-body">
                <!-- Steps Indicator -->
                <div class="steps-indicator">
                    <div class="step active" id="step1Indicator">
                        <div class="step-number">1</div>
                        <span>Email</span>
                    </div>
                    <div class="step-line"></div>
                    <div class="step" id="step2Indicator">
                        <div class="step-number">2</div>
                        <span>Verify</span>
                    </div>
                    <div class="step-line"></div>
                    <div class="step" id="step3Indicator">
                        <div class="step-number">3</div>
                        <span>Reset</span>
                    </div>
                </div>

                <!-- Success/Error Messages -->
                <div id="successAlert" class="alert alert-modern alert-success-modern d-none" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    <span id="successMessage"></span>
                </div>
                <div id="errorAlert" class="alert alert-modern alert-danger-modern d-none" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <span id="errorMessage"></span>
                </div>
                <div id="infoAlert" class="alert alert-modern alert-info-modern d-none" role="alert">
                    <i class="bi bi-info-circle me-2"></i>
                    <span id="infoMessage"></span>
                </div>

                <!-- Step 1: Email Verification -->
                <div class="step-content active" id="step1">
                    <form id="emailForm">
                        <div class="form-group-modern">
                            <label class="form-label-modern">
                                <i class="bi bi-envelope"></i>Email Address
                            </label>
                            <input type="email" class="form-control-modern" id="email" name="email" placeholder="Enter your registered email" required>
                        </div>
                        <button type="submit" class="btn-modern btn-primary-modern" id="sendCodeBtn">
                            <i class="bi bi-send"></i>
                            <span>Send Reset Code</span>
                        </button>
                    </form>
                </div>

                <!-- Step 2: Code Verification -->
                <div class="step-content" id="step2">
                    <form id="verifyForm">
                        <div class="form-group-modern">
                            <label class="form-label-modern">
                                <i class="bi bi-key"></i>Verification Code
                            </label>
                            <input type="text" class="form-control-modern" id="verificationCode" name="verificationCode" placeholder="Enter 6-digit code" maxlength="6" required>
                            <small class="text-muted">Check your email for the verification code</small>
                        </div>
                        <button type="submit" class="btn-modern btn-primary-modern" id="verifyCodeBtn">
                            <i class="bi bi-check-circle"></i>
                            <span>Verify Code</span>
                        </button>
                        <button type="button" class="btn-modern btn-outline-modern w-100 mt-2" onclick="resendCode()">
                            <i class="bi bi-arrow-clockwise"></i>
                            <span>Resend Code</span>
                        </button>
                    </form>
                </div>

                <!-- Step 3: New Password -->
                <div class="step-content" id="step3">
                    <form id="resetForm">
                        <div class="form-group-modern">
                            <label class="form-label-modern">
                                <i class="bi bi-lock"></i>New Password
                            </label>
                            <div class="password-toggle">
                                <input type="password" class="form-control-modern" id="newPassword" name="newPassword" placeholder="Create a strong password" required>
                                <button type="button" class="password-toggle-btn" onclick="togglePassword('newPassword')">
                                    <i class="bi bi-eye" id="newPasswordEye"></i>
                                </button>
                            </div>
                            <small class="text-muted">Password must be at least 8 characters long</small>
                        </div>
                        <div class="form-group-modern">
                            <label class="form-label-modern">
                                <i class="bi bi-lock-fill"></i>Confirm Password
                            </label>
                            <div class="password-toggle">
                                <input type="password" class="form-control-modern" id="confirmNewPassword" name="confirmNewPassword" placeholder="Confirm your new password" required>
                                <button type="button" class="password-toggle-btn" onclick="togglePassword('confirmNewPassword')">
                                    <i class="bi bi-eye" id="confirmNewPasswordEye"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn-modern btn-primary-modern" id="resetPasswordBtn">
                            <i class="bi bi-shield-check"></i>
                            <span>Reset Password</span>
                        </button>
                    </form>
                </div>

                <!-- Help Section -->
                <div class="help-section">
                    <div class="help-title">
                        <i class="bi bi-question-circle"></i>
                        Need Help?
                    </div>
                    <div class="help-text">
                        If you're having trouble accessing your account, contact our support team for assistance.
                    </div>
                    <div class="d-grid gap-2">
                        <a href="mailto:support@yp4kresort.com" class="btn-modern btn-outline-modern">
                            <i class="bi bi-envelope"></i>
                            Email Support
                        </a>
                        <a href="#" class="btn-modern btn-secondary-modern">
                            <i class="bi bi-telephone"></i>
                            Call Support
                        </a>
                    </div>
                </div>

                <!-- Footer -->
                <div class="auth-footer">
                    <p>Remember your password? <a href="http://localhost/YP4K_Resort/Authentication/login.php">Sign In</a></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentStep = 1;
        const userEmail = localStorage.getItem('resetEmail') || '';

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Check if there's a token in URL (direct link from email)
            const urlParams = new URLSearchParams(window.location.search);
            const token = urlParams.get('token');
            const email = urlParams.get('email');
            
            if (token && email) {
                localStorage.setItem('resetEmail', email);
                localStorage.setItem('resetToken', token);
                goToStep(3);
                showInfo('Token received. Please create your new password.');
            }
            
            // Check for URL parameters (success/error messages)
            const success = urlParams.get('success');
            const error = urlParams.get('error');
            
            if (success) {
                showSuccess(decodeURIComponent(success));
            }
            
            if (error) {
                showError(decodeURIComponent(error));
            }
        });

        // Email Form Submission
        document.getElementById('emailForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const submitBtn = document.getElementById('sendCodeBtn');
            
            // Show loading state
            submitBtn.classList.add('btn-loading');
            submitBtn.querySelector('span').textContent = 'Sending...';
            
            // Simulate API call
            setTimeout(() => {
                submitBtn.classList.remove('btn-loading');
                submitBtn.querySelector('span').textContent = 'Send Reset Code';
                
                // Store email
                localStorage.setItem('resetEmail', email);
                
                // Move to next step
                goToStep(2);
                showSuccess(`Reset code sent to ${email}. Please check your inbox.`);
            }, 2000);
        });

        // Verification Form Submission
        document.getElementById('verifyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const code = document.getElementById('verificationCode').value;
            const submitBtn = document.getElementById('verifyCodeBtn');
            
            // Show loading state
            submitBtn.classList.add('btn-loading');
            submitBtn.querySelector('span').textContent = 'Verifying...';
            
            // Simulate verification (accept any 6-digit code)
            setTimeout(() => {
                submitBtn.classList.remove('btn-loading');
                submitBtn.querySelector('span').textContent = 'Verify Code';
                
                if (code.length === 6) {
                    // Move to next step
                    goToStep(3);
                    showSuccess('Code verified successfully. Please create your new password.');
                } else {
                    showError('Invalid verification code. Please try again.');
                }
            }, 1500);
        });

        // Reset Password Form Submission
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmNewPassword').value;
            const submitBtn = document.getElementById('resetPasswordBtn');
            
            // Validation
            if (newPassword.length < 8) {
                showError('Password must be at least 8 characters long');
                return;
            }
            
            if (newPassword !== confirmPassword) {
                showError('Passwords do not match');
                return;
            }
            
            // Show loading state
            submitBtn.classList.add('btn-loading');
            submitBtn.querySelector('span').textContent = 'Resetting...';
            
            // Simulate API call
            setTimeout(() => {
                submitBtn.classList.remove('btn-loading');
                submitBtn.querySelector('span').textContent = 'Reset Password';
                
                // Clear stored data
                localStorage.removeItem('resetEmail');
                localStorage.removeItem('resetToken');
                
                // Show success and redirect
                showSuccess('Password reset successfully! Redirecting to login...');
                
                setTimeout(() => {
                    window.location.href = 'login.php?success=Password reset successfully';
                }, 2000);
            }, 2000);
        });

        // Navigation Functions
        function goToStep(step) {
            // Hide all steps
            document.querySelectorAll('.step-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Show current step
            document.getElementById(`step${step}`).classList.add('active');
            
            // Update indicators
            for (let i = 1; i <= 3; i++) {
                const indicator = document.getElementById(`step${i}Indicator`);
                if (i < step) {
                    indicator.classList.remove('active');
                    indicator.classList.add('completed');
                } else if (i === step) {
                    indicator.classList.add('active');
                    indicator.classList.remove('completed');
                } else {
                    indicator.classList.remove('active', 'completed');
                }
            }
            
            currentStep = step;
        }

        // Resend Code
        function resendCode() {
            const email = localStorage.getItem('resetEmail');
            if (email) {
                showInfo(`Reset code resent to ${email}. Please check your inbox.`);
            } else {
                showError('Please enter your email first.');
                goToStep(1);
            }
        }

        // Password Toggle
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const eye = document.getElementById(fieldId + 'Eye');
            
            if (field.type === 'password') {
                field.type = 'text';
                eye.classList.remove('bi-eye');
                eye.classList.add('bi-eye-slash');
            } else {
                field.type = 'password';
                eye.classList.remove('bi-eye-slash');
                eye.classList.add('bi-eye');
            }
        }

        // Alert Functions
        function showSuccess(message) {
            hideAllAlerts();
            const alert = document.getElementById('successAlert');
            document.getElementById('successMessage').textContent = message;
            alert.classList.remove('d-none');
            
            setTimeout(() => {
                alert.classList.add('d-none');
            }, 5000);
        }

        function showError(message) {
            hideAllAlerts();
            const alert = document.getElementById('errorAlert');
            document.getElementById('errorMessage').textContent = message;
            alert.classList.remove('d-none');
            
            setTimeout(() => {
                alert.classList.add('d-none');
            }, 5000);
        }

        function showInfo(message) {
            hideAllAlerts();
            const alert = document.getElementById('infoAlert');
            document.getElementById('infoMessage').textContent = message;
            alert.classList.remove('d-none');
            
            setTimeout(() => {
                alert.classList.add('d-none');
            }, 5000);
        }

        function hideAllAlerts() {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.classList.add('d-none');
            });
        }

        // Add input animations
        document.querySelectorAll('.form-control-modern').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });

        // Global keyboard shortcut for admin access
        document.addEventListener('keydown', function(event) {
            // Check for Ctrl + Shift + L
            if (event.ctrlKey && event.shiftKey && (event.key === 'l' || event.key === 'L')) {
                event.preventDefault();
                window.location.href = 'http://localhost/YP4K_Resort/Authentication/admin-login.php';
            }
        });
    </script>
</body>
</html>
