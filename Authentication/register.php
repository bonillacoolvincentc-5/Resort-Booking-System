<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - YP4K Resort</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/register-new.css">
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
                        <i class="bi bi-gem" style="font-size: 3rem; color: var(--accent-primary);"></i>
                        <h3 class="mt-2 fw-bold" style="color: var(--text-primary);">YP4K Resort</h3>
                        <p class="text-muted">Create Your Account</p>
                    </div>

                    <form action="../php/register_process.php" method="POST" id="registerForm">
                        <div class="row">
                            <!-- First Name -->
                            <div class="col-md-4 mb-3">
                                <label for="firstName" class="form-label fw-bold" style="color: var(--text-primary);">First Name</label>
                                <input class="form-control form-control-lg" type="text" id="firstName" name="firstName" placeholder="First name" required
                                       style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary);">
                            </div>

                            <!-- Middle Name (Optional) -->
                            <div class="col-md-4 mb-3">
                                <label for="middleName" class="form-label fw-bold" style="color: var(--text-primary);">Middle Name</label>
                                <input class="form-control form-control-lg" type="text" id="middleName" name="middleName" placeholder="Middle name (optional)"
                                       style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary);">
                            </div>

                            <!-- Last Name -->
                            <div class="col-md-4 mb-3">
                                <label for="lastName" class="form-label fw-bold" style="color: var(--text-primary);">Last Name</label>
                                <input class="form-control form-control-lg" type="text" id="lastName" name="lastName" placeholder="Last name" required
                                       style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary);">
                            </div>
                        </div>

                        <!-- Suffix (Optional) -->
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="suffix" class="form-label fw-bold" style="color: var(--text-primary);">
                                    Suffix
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
                            <label for="email" class="form-label fw-bold" style="color: var(--text-primary);">Email Address</label>
                            <input class="form-control form-control-lg" type="email" id="email" name="email" placeholder="Enter your email" required
                                   style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary);">
                        </div>

                        <!-- Phone -->
                        <div class="mb-3">
                            <label for="phone" class="form-label fw-bold" style="color: var(--text-primary);">Phone Number</label>
                            <input class="form-control form-control-lg" type="tel" id="phone" name="phone" placeholder="+63 (XXX) XXX-XXXX" required
                                   style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary);">
                        </div>

                        <!-- Address Information Section -->
                        <div class="mb-4">
                            <h5 class="mb-3" style="color: var(--text-primary); font-weight: 600;">
                                <i class="bi bi-geo-alt me-2" style="color: var(--accent-primary);"></i>
                                Address Information
                            </h5>
                            
                            <div class="row">
                                <!-- Region -->
                                <div class="col-md-6 mb-3">
                                    <label for="region" class="form-label fw-bold" style="color: var(--text-primary);">Region</label>
                                    <select class="form-select form-select-lg" id="region" name="region" required
                                            style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary);">
                                        <option value="">Select Region</option>
                                    </select>
                                </div>

                                <!-- Province -->
                                <div class="col-md-6 mb-3">
                                    <label for="province" class="form-label fw-bold" style="color: var(--text-primary);">Province</label>
                                    <select class="form-select form-select-lg" id="province" name="province" required disabled
                                            style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary);">
                                        <option value="">Select Province</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <!-- City/Municipality -->
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label fw-bold" style="color: var(--text-primary);">City / Municipality</label>
                                    <select class="form-select form-select-lg" id="city" name="city" required disabled
                                            style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary);">
                                        <option value="">Select City/Municipality</option>
                                    </select>
                                </div>

                                <!-- Barangay -->
                                <div class="col-md-6 mb-3">
                                    <label for="barangay" class="form-label fw-bold" style="color: var(--text-primary);">Barangay</label>
                                    <select class="form-select form-select-lg" id="barangay" name="barangay" required disabled
                                            style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary);">
                                        <option value="">Select Barangay</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Password with Eye Icon -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-bold" style="color: var(--text-primary);">Password</label>
                            <div class="input-group input-group-lg">
                                <input class="form-control" type="password" id="password" name="password" placeholder="Create a strong password" required
                                       style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary);">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password', 'eyeIcon1')"
                                        style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-secondary);">
                                    <i class="bi bi-eye" id="eyeIcon1"></i>
                                </button>
                            </div>
                            <small class="text-muted d-block mt-1" style="color: var(--text-primary);">Password must be at least 8 characters long</small>
                        </div>

                        <!-- Confirm Password with Eye Icon -->
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label fw-bold" style="color: var(--text-primary);">Confirm Password</label>
                            <div class="input-group input-group-lg">
                                <input class="form-control" type="password" id="confirmPassword" name="confirmPassword" placeholder="Re-enter your password" required
                                       style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary);">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('confirmPassword', 'eyeIcon2')"
                                        style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-secondary);">
                                    <i class="bi bi-eye" id="eyeIcon2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Terms & Conditions -->
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="agreeTerms" name="agreeTerms" required
                                   style="background: var(--bg-tertiary); border: 1px solid var(--border-color);">
                            <label class="form-check-label" for="agreeTerms" style="color: var(--text-secondary);">
                                I agree to the <a href="#" class="text-decoration-none fw-bold" style="color: var(--accent-primary);">Terms & Conditions</a>
                            </label>
                        </div>

                        <!-- Register Button -->
                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold mb-3" 
                                style="background: var(--gradient-primary); border: none;">
                            <i class="bi bi-person-plus"></i> Create Account
                        </button>

                        <!-- Divider -->
                        <div class="d-flex align-items-center my-3">
                            <hr class="flex-grow-1" style="border-color: var(--border-color);">
                            <span class="mx-2 text-muted">OR</span>
                            <hr class="flex-grow-1" style="border-color: var(--border-color);">
                        </div>

                        <!-- Login Link -->
                        <p class="text-center">
                            Already have an account? 
                            <a href="http://localhost/YP4K_Resort/Authentication/login.php" class="text-decoration-none fw-bold" style="color: var(--accent-primary);">Login here</a>
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
        // Philippines Address Dropdown Functionality
        const PSGC_API_BASE = 'https://psgc.gitlab.io/api';
        
        let regionsData = [];
        let provincesData = [];
        let citiesData = [];
        let barangaysData = [];

        // Initialize address dropdowns
        document.addEventListener('DOMContentLoaded', function() {
            loadRegions();
            setupEventListeners();
        });

        function setupEventListeners() {
            document.getElementById('region').addEventListener('change', handleRegionChange);
            document.getElementById('province').addEventListener('change', handleProvinceChange);
            document.getElementById('city').addEventListener('change', handleCityChange);
        }

        async function loadRegions() {
            try {
                const response = await fetch(`${PSGC_API_BASE}/regions/`);
                regionsData = await response.json();
                
                const regionSelect = document.getElementById('region');
                regionSelect.innerHTML = '<option value="">Select Region</option>';
                
                regionsData.forEach(region => {
                    const option = document.createElement('option');
                    option.value = region.code;
                    option.textContent = region.name;
                    regionSelect.appendChild(option);
                });
            } catch (error) {
                console.error('Error loading regions:', error);
                showError('Failed to load regions. Please try again.');
            }
        }

        async function handleRegionChange() {
            const regionCode = this.value;
            const provinceSelect = document.getElementById('province');
            const citySelect = document.getElementById('city');
            const barangaySelect = document.getElementById('barangay');
            
            // Reset dependent dropdowns
            provinceSelect.innerHTML = '<option value="">Select Province</option>';
            provinceSelect.disabled = true;
            citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
            citySelect.disabled = true;
            barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
            barangaySelect.disabled = true;
            
            if (!regionCode) return;
            
            try {
                const response = await fetch(`${PSGC_API_BASE}/regions/${regionCode}/provinces/`);
                provincesData = await response.json();
                
                provinceSelect.disabled = false;
                
                provincesData.forEach(province => {
                    const option = document.createElement('option');
                    option.value = province.code;
                    option.textContent = province.name;
                    provinceSelect.appendChild(option);
                });
            } catch (error) {
                console.error('Error loading provinces:', error);
                showError('Failed to load provinces. Please try again.');
            }
        }

        async function handleProvinceChange() {
            const provinceCode = this.value;
            const citySelect = document.getElementById('city');
            const barangaySelect = document.getElementById('barangay');
            
            // Reset dependent dropdowns
            citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
            citySelect.disabled = true;
            barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
            barangaySelect.disabled = true;
            
            if (!provinceCode) return;
            
            try {
                // Load cities and municipalities for the province
                const [citiesResponse, municipalitiesResponse] = await Promise.all([
                    fetch(`${PSGC_API_BASE}/provinces/${provinceCode}/cities-municipalities/`)
                ]);
                
                const citiesAndMunicipalities = await citiesResponse.json();
                citiesData = citiesAndMunicipalities;
                
                citySelect.disabled = false;
                
                citiesData.forEach(city => {
                    const option = document.createElement('option');
                    option.value = city.code;
                    option.textContent = city.name;
                    citySelect.appendChild(option);
                });
            } catch (error) {
                console.error('Error loading cities/municipalities:', error);
                showError('Failed to load cities/municipalities. Please try again.');
            }
        }

        async function handleCityChange() {
            const cityCode = this.value;
            const barangaySelect = document.getElementById('barangay');
            
            // Reset barangay dropdown
            barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
            barangaySelect.disabled = true;
            
            if (!cityCode) return;
            
            try {
                const response = await fetch(`${PSGC_API_BASE}/cities-municipalities/${cityCode}/barangays/`);
                barangaysData = await response.json();
                
                barangaySelect.disabled = false;
                
                barangaysData.forEach(barangay => {
                    const option = document.createElement('option');
                    option.value = barangay.code;
                    option.textContent = barangay.name;
                    barangaySelect.appendChild(option);
                });
            } catch (error) {
                console.error('Error loading barangays:', error);
                showError('Failed to load barangays. Please try again.');
            }
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

        // Form validation with OTP verification
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const region = document.getElementById('region').value;
            const province = document.getElementById('province').value;
            const city = document.getElementById('city').value;
            const barangay = document.getElementById('barangay').value;

            // Validate address fields
            if (!region || !province || !city || !barangay) {
                showError('Please complete all address fields.');
                return false;
            }

            if (password !== confirmPassword) {
                showError('Passwords do not match!');
                return false;
            }

            if (password.length < 8) {
                showError('Password must be at least 8 characters long!');
                return false;
            }

            // Submit form for OTP verification
            submitRegistration();
        });

        function submitRegistration() {
            const formData = new FormData(document.getElementById('registerForm'));
            const submitBtn = document.querySelector('button[type="submit"]');
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Processing...';

            fetch('../phpAuthentication/register_process.php', {
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
                console.error('Registration error:', error);
                showError('Registration failed. Please try again.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-person-plus me-2"></i>Create Account';
            });
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
