<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin Account - YP4K Resort</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin-register.css">
</head>
<body>
    <!-- Background Animation -->
    <div class="bg-animation">
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        <div class="floating-shape shape-3"></div>
    </div>

    <!-- Main Container -->
    <div class="container d-flex justify-content-center align-items-center min-vh-100 py-5">
        <div class="col-md-6">
            <div class="card shadow-lg border-0" style="border-radius: 15px;">
                <div class="card-body p-5" style="background: linear-gradient(135deg, var(--bg-card) 0%, var(--bg-tertiary) 100%);">
                    <!-- Logo Section -->
                    <div class="text-center mb-4">
                        <i class="bi bi-shield-plus" style="font-size: 3rem; color: var(--accent-secondary);"></i>
                        <h3 class="mt-2 fw-bold" style="color: var(--text-primary);">YP4K Resort</h3>
                        <p class="text-muted">Create Admin Account</p>
                    </div>

                    <form action="../phpAuthentication/register_process_new.php" method="POST" id="adminRegisterForm">
                        <div class="row">
                            <!-- First Name -->
                            <div class="col-md-4 mb-3">
                                <label for="firstName" class="form-label fw-bold" style="color: var(--text-primary);">
                                    <i class="bi bi-person me-2" style="color: var(--accent-secondary);"></i>First Name
                                </label>
                                <input class="form-control form-control-lg" type="text" id="firstName" name="first_name" placeholder="First name" required
                                       style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary);">
                            </div>

                            <!-- Middle Name (Optional) -->
                            <div class="col-md-4 mb-3">
                                <label for="middleName" class="form-label fw-bold" style="color: var(--text-primary);">
                                    <i class="bi bi-person me-2" style="color: var(--accent-secondary);"></i>Middle Name
                                </label>
                                <input class="form-control form-control-lg" type="text" id="middleName" name="middle_name" placeholder="Middle name (optional)"
                                       style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary);">
                            </div>

                            <!-- Last Name -->
                            <div class="col-md-4 mb-3">
                                <label for="lastName" class="form-label fw-bold" style="color: var(--text-primary);">
                                    <i class="bi bi-person me-2" style="color: var(--accent-secondary);"></i>Last Name
                                </label>
                                <input class="form-control form-control-lg" type="text" id="lastName" name="last_name" placeholder="Last name" required
                                       style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary);">
                            </div>
                        </div>

                        <!-- Suffix (Optional) -->
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="suffix" class="form-label fw-bold" style="color: var(--text-primary);">
                                    <i class="bi bi-person-badge me-2" style="color: var(--accent-secondary);"></i>Suffix
                                </label>
                                <select class="form-select form-select-lg" id="suffix" name="suffix"
                                        style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary);">
                                    <option value="">Select suffix</option>
                                    <option value="Jr.">Jr.</option>
                                    <option value="Sr.">Sr.</option>
                                    <option value="I">I</option>
                                    <option value="II">II</option>
                                    <option value="III">III</option>
                                    <option value="IV">IV</option>
                                    <option value="V">V</option>
                                </select>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-bold" style="color: var(--text-primary);">
                                <i class="bi bi-envelope me-2" style="color: var(--accent-secondary);"></i>Admin Email
                            </label>
                            <input class="form-control form-control-lg" type="email" id="email" name="email" placeholder="Enter admin email" required
                                   style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary);">
                        </div>

                        <!-- Phone -->
                        <div class="mb-3">
                            <label for="phone" class="form-label fw-bold" style="color: var(--text-primary);">
                                <i class="bi bi-telephone me-2" style="color: var(--accent-secondary);"></i>Phone Number
                            </label>
                            <input class="form-control form-control-lg" type="tel" id="phone" name="phone_number" placeholder="09XXXXXXXXX" required
                                   style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary);">
                        </div>

                        <!-- Hidden user type field -->
                        <input type="hidden" name="user_type" value="admin">

                        <!-- Password with Eye Icon -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-bold" style="color: var(--text-primary);">
                                <i class="bi bi-lock me-2" style="color: var(--accent-secondary);"></i>Password
                            </label>
                            <div class="input-group input-group-lg">
                                <input class="form-control" type="password" id="password" name="password" placeholder="Create a strong password" required
                                       style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary);">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password', 'eyeIcon1')"
                                        style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-secondary);">
                                    <i class="bi bi-eye" id="eyeIcon1"></i>
                                </button>
                            </div>
                            <div id="password-checker" style="font-size: 0.72rem; margin-top: 0.6rem; display: flex; flex-wrap: wrap; gap: 0.4rem;">
                                <div id="req-length" style="color: rgba(255,255,255,0.5); background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.05); padding: 0.2rem 0.6rem; border-radius: 12px; display: flex; align-items: center; gap: 0.25rem; transition: all 0.3s ease;"><i class="bi bi-circle"></i> 8+ characters</div>
                                <div id="req-capital" style="color: rgba(255,255,255,0.5); background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.05); padding: 0.2rem 0.6rem; border-radius: 12px; display: flex; align-items: center; gap: 0.25rem; transition: all 0.3s ease;"><i class="bi bi-circle"></i> 1 capital letter</div>
                                <div id="req-number" style="color: rgba(255,255,255,0.5); background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.05); padding: 0.2rem 0.6rem; border-radius: 12px; display: flex; align-items: center; gap: 0.25rem; transition: all 0.3s ease;"><i class="bi bi-circle"></i> 1 number</div>
                                <div id="req-special" style="color: rgba(255,255,255,0.5); background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.05); padding: 0.2rem 0.6rem; border-radius: 12px; display: flex; align-items: center; gap: 0.25rem; transition: all 0.3s ease;"><i class="bi bi-circle"></i> 1 special char</div>
                            </div>
                        </div>

                        <!-- Confirm Password with Eye Icon -->
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label fw-bold" style="color: var(--text-primary);">
                                <i class="bi bi-lock-fill me-2" style="color: var(--accent-secondary);"></i>Confirm Password
                            </label>
                            <div class="input-group input-group-lg">
                                <input class="form-control" type="password" id="confirmPassword" name="confirm_password" placeholder="Re-enter your password" required
                                       style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary);">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('confirmPassword', 'eyeIcon2')"
                                        style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-secondary);">
                                    <i class="bi bi-eye" id="eyeIcon2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Terms & Conditions -->
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="agreeTerms" name="agree_terms" required
                                   style="background: var(--bg-tertiary); border: 1px solid var(--border-color);">
                            <label class="form-check-label" for="agreeTerms" style="color: var(--text-secondary);">
                                I agree to the <a href="#" class="text-decoration-none fw-bold" style="color: var(--accent-secondary);">Admin Terms & Conditions</a>
                            </label>
                        </div>

                        <!-- Register Button -->
                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold mb-3" 
                                style="background: linear-gradient(135deg, var(--accent-secondary), var(--accent-primary)); border: none;">
                            <i class="bi bi-shield-plus"></i> Create Admin Account
                        </button>

                        <!-- Divider -->
                        <div class="d-flex   align-items-center my-3">
                            <hr class="flex-grow-1" style="border-color: var(--border-color);">
                            <span class="mx-2 text-muted">OR</span>
                            <hr class="flex-grow-1" style="border-color: var(--border-color);">
                        </div>

                        <!-- Admin Login Link -->
                        <p class="text-center">
                            Already have admin account? 
                            <a href="http://localhost/YP4K_Resort/Authentication/admin-login.php" class="text-decoration-none fw-bold" style="color: var(--accent-secondary);">Admin Login</a>
                        </p>

                        <!-- User Register Link -->
                        <p class="text-center mt-3">
                            <i class="bi bi-person me-1" style="color: var(--accent-primary);"></i>
                            User account? 
                            <a href="http://localhost/YP4K_Resort/Authentication/register.php" class="text-decoration-none fw-bold" style="color: var(--accent-primary);">Register as User</a>
                        </p>
                    </form>

                    <!-- Error Message -->
                    <div id="errorMessage" class="alert alert-danger d-none mt-3" role="alert"
                         style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: var(--accent-danger);">
                    </div>
                    <div id="successMessage" class="alert alert-success d-none mt-3" role="alert"
                         style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: var(--accent-success);">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Exact function from original register.html
        function togglePasswordVisibility(fieldId, iconId) {
            const passwordField = document.getElementById(fieldId);
            const eyeIcon = document.getElementById(iconId);

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.classList.remove('bi-eye');
                eyeIcon.classList.add('bi-eye-slash');
            } else {
                passwordField.type = 'password';
                eyeIcon.classList.remove('bi-eye-slash');
                eyeIcon.classList.add('bi-eye');
            }
        }

        function setupPasswordChecker() {
            const passInput = document.getElementById('password');
            if (!passInput) return;

            passInput.addEventListener('input', function() {
                const p = this.value;
                
                function updateReq(elementId, isValid, text) {
                    const el = document.getElementById(elementId);
                    if (!el) return;
                    if (isValid) {
                        el.style.color = '#84ea3b';
                        el.style.background = 'rgba(132, 234, 59, 0.1)';
                        el.style.borderColor = 'rgba(132, 234, 59, 0.3)';
                        el.innerHTML = '<i class="bi bi-check2-circle"></i> ' + text;
                    } else {
                        el.style.color = 'rgba(255,255,255,0.5)';
                        el.style.background = 'rgba(255,255,255,0.05)';
                        el.style.borderColor = 'rgba(255,255,255,0.05)';
                        el.innerHTML = '<i class="bi bi-circle"></i> ' + text;
                    }
                }

                updateReq('req-length', p.length >= 8, '8+ characters');
                updateReq('req-capital', /[A-Z]/.test(p), '1 capital letter');
                updateReq('req-number', /\d/.test(p), '1 number');
                updateReq('req-special', /[!@#$%^&*(),.?":{}|<>]/.test(p), '1 special char');
            });
        }
        document.addEventListener('DOMContentLoaded', setupPasswordChecker);

        // Form validation - Updated with agree_terms check
        document.getElementById('adminRegisterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (password !== confirmPassword) {
                showError('Passwords do not match!');
                return false;
            }

            if (password.length < 8 || !/[A-Z]/.test(password) || !/\d/.test(password) || !/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
                showError('Password does not meet all requirements (min 8 chars, 1 uppercase, 1 number, 1 special character)!');
                return false;
            }

            if (!document.getElementById('agreeTerms').checked) {
                showError('You must agree to the admin terms and conditions');
                return false;
            }

            // Submit form for OTP verification
            submitAdminRegistration();
        });

        function submitAdminRegistration() {
            const formData = new FormData(document.getElementById('adminRegisterForm'));
            const submitBtn = document.querySelector('button[type="submit"]');
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Processing...';

            fetch('../php/admin_register_process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess(data.message);
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 2000);
                } else {
                    showError(data.message);
                }
            })
            .catch(error => {
                console.error('Admin registration error:', error);
                showError('Admin registration failed. Please try again.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-shield-plus"></i> Create Admin Account';
            });
        }

        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.textContent = message;
            errorDiv.classList.remove('d-none');
            
            setTimeout(() => {
                errorDiv.classList.add('d-none');
            }, 5000);
        }

        function showSuccess(message) {
            const successDiv = document.getElementById('successMessage');
            successDiv.textContent = message;
            successDiv.classList.remove('d-none');
            
            setTimeout(() => {
                successDiv.classList.add('d-none');
            }, 5000);
        }

        // Display error messages from server - Exact from original
        const urlParams = new URLSearchParams(window.location.search);
        const error = urlParams.get('error');
        if (error) {
            document.getElementById('errorMessage').textContent = decodeURIComponent(error);
            document.getElementById('errorMessage').classList.remove('d-none');
        }

        const success = urlParams.get('success');
        if (success) {
            document.getElementById('successMessage').textContent = decodeURIComponent(success);
            document.getElementById('successMessage').classList.remove('d-none');
        }
    </script>
</body>
</html>
