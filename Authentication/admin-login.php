<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - YP4K Resort</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin-login.css">
</head>
<body>
    <!-- Background Animation -->
    <div class="bg-animation">
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        <div class="floating-shape shape-3"></div>
    </div>

    <!-- Main Container - Exact layout from original -->
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="col-md-5">
            <div class="card shadow-lg border-0" style="border-radius: 15px;">
                <div class="card-body p-5" style="background: linear-gradient(135deg, var(--bg-card) 0%, var(--bg-tertiary) 100%);">
                    <!-- Logo Section - Exact structure -->
                    <div class="text-center mb-4">
                        <i class="bi bi-shield-lock" style="font-size: 3rem; color: var(--accent-secondary);"></i>
                        <h3 class="mt-2 fw-bold" style="color: var(--text-primary);">YP4K Resort</h3>
                        <p class="text-muted">Admin Portal</p>
                    </div>

                    <form action="../php/admin_login_process.php" method="POST" id="adminLoginForm">
                        <!-- Email - Exact structure -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-bold" style="color: var(--text-primary);">
                                <i class="bi bi-envelope me-2" style="color: var(--accent-secondary);"></i>Admin Email
                            </label>
                            <input class="form-control form-control-lg" type="email" id="email" name="email" placeholder="Enter admin email" required
                                   style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary);">
                        </div>

                        <!-- Password with Eye Icon - Exact structure -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-bold" style="color: var(--text-primary);">
                                <i class="bi bi-lock me-2" style="color: var(--accent-secondary);"></i>Admin Password
                            </label>
                            <div class="input-group input-group-lg">
                                <input class="form-control" type="password" id="password" name="password" placeholder="Enter admin password" required
                                       style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary);">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword" onclick="togglePasswordVisibility()"
                                        style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-secondary);">
                                    <i class="bi bi-eye" id="eyeIcon"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Remember Me & Forgot Password - Exact structure -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="rememberMe" name="remember"
                                       style="background: var(--bg-tertiary); border: 1px solid var(--border-color);">
                                <label class="form-check-label" for="rememberMe" style="color: var(--text-secondary);">Remember me</label>
                            </div>
                            <a href="http://localhost/YP4K_Resort/Authentication/forgot-pass.php" class="text-decoration-none fw-bold" style="color: var(--accent-secondary);">Forgot Password?</a>
                        </div>

                        <!-- Login Button - Exact structure -->
                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold mb-3" 
                                style="background: linear-gradient(135deg, var(--accent-secondary), var(--accent-primary)); border: none;">
                            <i class="bi bi-shield-check"></i> Admin Login
                        </button>

                        <!-- Divider - Exact structure -->
                        <div class="d-flex align-items-center my-3">
                            <hr class="flex-grow-1" style="border-color: var(--border-color);">
                            <span class="mx-2 text-muted">OR</span>
                            <hr class="flex-grow-1" style="border-color: var(--border-color);">
                        </div>

                        <!-- User Login Link -->
                        <p class="text-center">
                            <i class="bi bi-person me-1" style="color: var(--accent-primary);"></i>
                            User account? 
                            <a href="http://localhost/YP4K_Resort/Authentication/login.php" class="text-decoration-none fw-bold" style="color: var(--accent-primary);">Sign in as User</a>
                        </p>

                        <!-- Admin Register Link -->
                        <p class="text-center mt-3">
                            Need admin access? 
                            <a href="http://localhost/YP4K_Resort/Authentication/admin-register.php" class="text-decoration-none fw-bold" style="color: var(--accent-secondary);">Register Admin Account</a>
                        </p>
                    </form>

                    <!-- Error Message - Exact structure -->
                    <div id="errorMessage" class="alert alert-danger d-none mt-3" role="alert"
                         style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: var(--accent-danger);">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Exact function from original login.html
        function togglePasswordVisibility() {
            const passwordField = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

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
        // Optional: Display error messages from server - Exact from original
        const urlParams = new URLSearchParams(window.location.search);
        const error = urlParams.get('error');
        if (error) {
            document.getElementById('errorMessage').textContent = decodeURIComponent(error);
            document.getElementById('errorMessage').classList.remove('d-none');
        }
    </script>
</body>
</html>
