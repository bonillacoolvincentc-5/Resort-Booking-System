<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — YP4K Resort</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/resort-theme.css">
    <link rel="stylesheet" href="../css/auth-resort.css">
    <style>
        body { justify-content: flex-start; padding: 2rem 1rem; }
        .auth-card { max-width: 640px; }
        .section-divider-small {
            display: flex; align-items: center; gap: 0.75rem; margin: 1.5rem 0 1.25rem;
        }
        .section-divider-small::before, .section-divider-small::after {
            content:''; flex:1; height:1px; background: var(--border);
        }
        .section-divider-small span {
            font-size: 0.72rem; font-weight: 700; letter-spacing: 0.1em;
            text-transform: uppercase; color: var(--accent-2);
            display: flex; align-items: center; gap: 0.35rem; white-space: nowrap;
        }
        .select-resort option { background: #0D1810; }
    </style>
</head>
<body>
<div class="resort-bg"></div>
<div class="particles">
    <div class="particle"></div><div class="particle"></div>
    <div class="particle"></div><div class="particle"></div>
    <div class="particle"></div><div class="particle"></div>
</div>

<div style="position:relative;z-index:10;width:100%;display:flex;justify-content:center;align-items:flex-start;min-height:100vh;padding:3rem 1rem 4rem;">
    <div class="auth-card" style="max-width:640px;">
        <!-- Logo -->
        <div class="auth-logo">
            <div class="auth-logo-icon"><i class="bi bi-gem"></i></div>
            <h4>YP4K Resort</h4>
            <p>Create your account to start booking</p>
        </div>

        <!-- Alerts -->
        <div id="errorMessage" class="auth-alert auth-alert-error" style="display:none;">
            <i class="bi bi-exclamation-circle"></i><span id="errorText"></span>
        </div>
        <div id="successMessage" class="auth-alert auth-alert-success" style="display:none;">
            <i class="bi bi-check-circle"></i><span id="successText"></span>
        </div>

        <!-- Register Form — all fields, names, and backend action preserved -->
        <form action="../phpAuthentication/register_process.php" method="POST" id="registerForm">

            <!-- Name fields -->
            <div class="section-divider-small"><span><i class="bi bi-person"></i> Personal Information</span></div>
            <div class="row g-3 mb-0">
                <div class="col-md-4">
                    <div class="auth-field">
                        <label for="firstName" class="auth-label">First Name</label>
                        <input class="auth-input" type="text" id="firstName" name="firstName" placeholder="First name" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="auth-field">
                        <label for="middleName" class="auth-label">Middle Name</label>
                        <input class="auth-input" type="text" id="middleName" name="middleName" placeholder="Optional">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="auth-field">
                        <label for="lastName" class="auth-label">Last Name</label>
                        <input class="auth-input" type="text" id="lastName" name="lastName" placeholder="Last name" required>
                    </div>
                </div>
            </div>

            <div class="auth-field">
                <label for="suffix" class="auth-label">Suffix (Optional)</label>
                <select class="auth-input select-resort" id="suffix" name="suffix">
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

            <div class="auth-field">
                <label for="email" class="auth-label">Email Address</label>
                <input class="auth-input" type="email" id="email" name="email" placeholder="you@example.com" required autocomplete="email">
            </div>

            <div class="auth-field">
                <label for="phone" class="auth-label">Phone Number</label>
                <input class="auth-input" type="tel" id="phone" name="phone" placeholder="+63 (XXX) XXX-XXXX" required>
            </div>

            <!-- Address -->
            <div class="section-divider-small"><span><i class="bi bi-geo-alt"></i> Address Information</span></div>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="auth-field">
                        <label for="region" class="auth-label">Region</label>
                        <select class="auth-input select-resort" id="region" name="region" required>
                            <option value="">Select Region</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="auth-field">
                        <label for="province" class="auth-label">Province</label>
                        <select class="auth-input select-resort" id="province" name="province" required disabled>
                            <option value="">Select Province</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="auth-field">
                        <label for="city" class="auth-label">City / Municipality</label>
                        <select class="auth-input select-resort" id="city" name="city" required disabled>
                            <option value="">Select City/Municipality</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="auth-field">
                        <label for="barangay" class="auth-label">Barangay</label>
                        <select class="auth-input select-resort" id="barangay" name="barangay" required disabled>
                            <option value="">Select Barangay</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Password -->
            <div class="section-divider-small"><span><i class="bi bi-shield-lock"></i> Security</span></div>
            <div class="auth-field">
                <label for="password" class="auth-label">Password</label>
                <div class="auth-input-group">
                    <input class="auth-input" type="password" id="password" name="password" placeholder="Create a strong password (min 8 chars)" required>
                    <button type="button" class="auth-eye-btn" onclick="togglePasswordVisibility('password', 'eyeIcon1')">
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

            <div class="auth-field">
                <label for="confirmPassword" class="auth-label">Confirm Password</label>
                <div class="auth-input-group">
                    <input class="auth-input" type="password" id="confirmPassword" name="confirmPassword" placeholder="Re-enter your password" required>
                    <button type="button" class="auth-eye-btn" onclick="togglePasswordVisibility('confirmPassword', 'eyeIcon2')">
                        <i class="bi bi-eye" id="eyeIcon2"></i>
                    </button>
                </div>
            </div>

            <!-- Terms -->
            <div style="display:flex;align-items:flex-start;gap:0.6rem;margin-bottom:1.5rem;">
                <input class="auth-check" type="checkbox" id="agreeTerms" name="agreeTerms" required style="margin-top:3px;">
                <label for="agreeTerms" style="color:var(--text-muted);font-size:0.85rem;cursor:pointer;line-height:1.5;">
                    I agree to the <a href="#" class="auth-link">Terms &amp; Conditions</a> and
                    <a href="#" class="auth-link">Privacy Policy</a>
                </label>
            </div>

            <button type="submit" class="auth-submit">
                <i class="bi bi-person-plus"></i>
                Create Account
            </button>
        </form>

        <div class="auth-divider"><span>OR</span></div>
        <p class="auth-text">Already have an account? <a href="login.php" class="auth-link">Sign in here</a></p>
        <div class="text-center mt-3">
            <a href="../index.html" class="auth-back"><i class="bi bi-arrow-left"></i> Back to Home</a>
        </div>
    </div>
</div>

<script>
    const PSGC_API_BASE = 'https://psgc.gitlab.io/api';

    document.addEventListener('DOMContentLoaded', function() {
        loadRegions();
        document.getElementById('region').addEventListener('change', handleRegionChange);
        document.getElementById('province').addEventListener('change', handleProvinceChange);
        document.getElementById('city').addEventListener('change', handleCityChange);

        document.getElementById('password').addEventListener('input', function() {
            const p = this.value;
            
            function updateReq(elementId, isValid, text) {
                const el = document.getElementById(elementId);
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
    });

    async function loadRegions() {
        try {
            const response = await fetch(`${PSGC_API_BASE}/regions/`);
            const regionsData = await response.json();
            const regionSelect = document.getElementById('region');
            regionSelect.innerHTML = '<option value="">Select Region</option>';
            regionsData.forEach(region => {
                const option = document.createElement('option');
                option.value = region.code;
                option.textContent = region.name;
                regionSelect.appendChild(option);
            });
        } catch (error) { showError('Failed to load regions. Please try again.'); }
    }

    async function handleRegionChange() {
        const regionCode = this.value;
        resetDropdown('province', 'Select Province');
        resetDropdown('city', 'Select City/Municipality');
        resetDropdown('barangay', 'Select Barangay');
        if (!regionCode) return;
        try {
            const response = await fetch(`${PSGC_API_BASE}/regions/${regionCode}/provinces/`);
            const provincesData = await response.json();
            const provinceSelect = document.getElementById('province');
            provinceSelect.disabled = false;
            provincesData.forEach(p => { const o = document.createElement('option'); o.value = p.code; o.textContent = p.name; provinceSelect.appendChild(o); });
        } catch (e) { showError('Failed to load provinces.'); }
    }

    async function handleProvinceChange() {
        const provinceCode = this.value;
        resetDropdown('city', 'Select City/Municipality');
        resetDropdown('barangay', 'Select Barangay');
        if (!provinceCode) return;
        try {
            const response = await fetch(`${PSGC_API_BASE}/provinces/${provinceCode}/cities-municipalities/`);
            const citiesData = await response.json();
            const citySelect = document.getElementById('city');
            citySelect.disabled = false;
            citiesData.forEach(c => { const o = document.createElement('option'); o.value = c.code; o.textContent = c.name; citySelect.appendChild(o); });
        } catch (e) { showError('Failed to load cities.'); }
    }

    async function handleCityChange() {
        const cityCode = this.value;
        resetDropdown('barangay', 'Select Barangay');
        if (!cityCode) return;
        try {
            const response = await fetch(`${PSGC_API_BASE}/cities-municipalities/${cityCode}/barangays/`);
            const barangaysData = await response.json();
            const barangaySelect = document.getElementById('barangay');
            barangaySelect.disabled = false;
            barangaysData.forEach(b => { const o = document.createElement('option'); o.value = b.code; o.textContent = b.name; barangaySelect.appendChild(o); });
        } catch (e) { showError('Failed to load barangays.'); }
    }

    function resetDropdown(id, placeholder) {
        const sel = document.getElementById(id);
        sel.innerHTML = `<option value="">${placeholder}</option>`;
        sel.disabled = true;
    }

    function showError(message) {
        document.getElementById('errorText').textContent = message;
        const el = document.getElementById('errorMessage');
        el.style.display = 'flex';
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(() => el.style.display = 'none', 7000);
    }

    function showSuccess(message) {
        document.getElementById('successText').textContent = message;
        const el = document.getElementById('successMessage');
        el.style.display = 'flex';
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(() => el.style.display = 'none', 7000);
    }

    function togglePasswordVisibility(fieldId, iconId) {
        const field = document.getElementById(fieldId);
        const eye = document.getElementById(iconId);
        if (field.type === 'password') {
            field.type = 'text';
            eye.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            field.type = 'password';
            eye.classList.replace('bi-eye-slash', 'bi-eye');
        }
    }

    document.getElementById('registerForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        const region = document.getElementById('region').value;
        const province = document.getElementById('province').value;
        const city = document.getElementById('city').value;
        const barangay = document.getElementById('barangay').value;

        if (!region || !province || !city || !barangay) { showError('Please complete all address fields.'); return; }
        if (password !== confirmPassword) { showError('Passwords do not match!'); return; }
        if (password.length < 8 || !/[A-Z]/.test(password) || !/\d/.test(password) || !/[!@#$%^&*(),.?":{}|<>]/.test(password)) { 
            showError('Password does not meet all requirements!'); 
            return; 
        }

        submitRegistration();
    });

    function submitRegistration() {
        const formData = new FormData(document.getElementById('registerForm'));
        
        // Convert numerical dropdown codes to actual location names before sending to backend
        const regSel = document.getElementById('region');
        const provSel = document.getElementById('province');
        const citySel = document.getElementById('city');
        const barSel = document.getElementById('barangay');
        if (regSel.selectedIndex > 0) formData.set('region', regSel.options[regSel.selectedIndex].text);
        if (provSel.selectedIndex > 0) formData.set('province', provSel.options[provSel.selectedIndex].text);
        if (citySel.selectedIndex > 0) {
            formData.set('city', citySel.options[citySel.selectedIndex].text);
            formData.set('city_municipality', citySel.options[citySel.selectedIndex].text);
        }
        if (barSel.selectedIndex > 0) formData.set('barangay', barSel.options[barSel.selectedIndex].text);

        const submitBtn = document.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';

        fetch('../phpAuthentication/register_process.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showSuccess(data.message);
                setTimeout(() => window.location.href = data.redirect, 2000);
            } else { showError(data.message); }
        })
        .catch(() => showError('Registration failed. Please try again.'))
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-person-plus"></i> Create Account';
        });
    }

    // URL params
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('error')) showError(decodeURIComponent(urlParams.get('error')));
    if (urlParams.get('success')) showSuccess(decodeURIComponent(urlParams.get('success')));

    document.addEventListener('keydown', e => {
        if (e.ctrlKey && e.shiftKey && (e.key === 'l' || e.key === 'L')) {
            e.preventDefault(); window.location.href = 'admin-login.php';
        }
    });
</script>
</body>
</html>
