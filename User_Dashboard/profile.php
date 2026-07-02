<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - YP4K Resort</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/resort-theme.css">
    <link rel="stylesheet" href="../css/dashboard-resort.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --bg-tertiary: #334155;
            --bg-card: rgba(30, 41, 59, 0.8);
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --accent-primary: #3b82f6;
            --accent-secondary: #8b5cf6;
            --accent-success: #10b981;
            --accent-warning: #f59e0b;
            --accent-danger: #ef4444;
            --border-color: rgba(51, 65, 85, 0.5);
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --gradient-primary: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            --gradient-dark: linear-gradient(135deg, var(--bg-primary), var(--bg-secondary));
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 50%, var(--bg-tertiary) 100%);
            color: var(--text-primary);
            min-height: 100vh;
            line-height: 1.6;
        }

        /* Modern Navigation */
        .navbar-modern {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--text-primary) !important;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .navbar-brand i {
            color: var(--accent-primary);
            font-size: 1.5rem;
        }

        .nav-link {
            color: var(--text-secondary) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            margin: 0 0.25rem;
        }

        .nav-link:hover {
            color: var(--text-primary) !important;
            background: rgba(59, 130, 246, 0.1);
        }

        .nav-link.active {
            color: var(--text-primary) !important;
            background: var(--accent-primary);
        }

        .nav-link.btn-logout {
            background: var(--accent-danger);
            color: white;
            border: none;
            padding: 0.5rem 1rem !important;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
        }

        .nav-link.btn-logout:hover {
            background: #dc2626;
            transform: translateY(-1px);
            color: white !important;
        }

        /* Main Container */
        .profile-container {
            padding: 2rem 0;
        }

        /* Page Header */
        .page-header {
            background: var(--bg-card);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--gradient-primary);
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--text-primary), var(--accent-primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-subtitle {
            color: var(--text-secondary);
            font-size: 1rem;
        }

        /* Profile Sidebar */
        .profile-sidebar {
            background: var(--bg-card);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 2rem;
            text-align: center;
            position: sticky;
            top: 100px;
            height: fit-content;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 1rem;
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 3rem;
            color: white;
            box-shadow: var(--shadow-lg);
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .profile-avatar:hover {
            transform: scale(1.05);
            box-shadow: var(--shadow-xl);
        }

        .profile-avatar::after {
            content: '\f030';
            font-family: 'Bootstrap Icons';
            position: absolute;
            bottom: -5px;
            right: -5px;
            background: var(--accent-success);
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            color: white;
            border: 3px solid var(--bg-card);
        }

        .profile-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            color: var(--text-primary);
        }

        .profile-email {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .profile-badges {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .profile-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .badge-gold {
            background: rgba(245, 158, 11, 0.2);
            color: var(--accent-warning);
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .badge-verified {
            background: rgba(16, 185, 129, 0.2);
            color: var(--accent-success);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .profile-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Main Content */
        .profile-main {
            background: var(--bg-card);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            overflow: hidden;
        }

        /* Tabs */
        .profile-tabs {
            display: flex;
            background: var(--bg-tertiary);
            border-bottom: 1px solid var(--border-color);
        }

        .profile-tab {
            padding: 1rem 1.5rem;
            background: none;
            border: none;
            color: var(--text-secondary);
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .profile-tab:hover {
            color: var(--text-primary);
            background: rgba(59, 130, 246, 0.1);
        }

        .profile-tab.active {
            color: var(--text-primary);
            background: var(--bg-card);
        }

        .profile-tab.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--gradient-primary);
        }

        /* Tab Content */
        .tab-content {
            padding: 2rem;
        }

        .tab-pane {
            display: none;
        }

        .tab-pane.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Form Groups */
        .form-group-modern {
            margin-bottom: 1.5rem;
        }

        .form-label-modern {
            color: var(--text-primary);
            font-weight: 500;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-label-modern i {
            color: var(--accent-primary);
            font-size: 0.85rem;
        }

        .form-control-modern {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control-modern:focus {
            background: var(--bg-tertiary);
            border-color: var(--accent-primary);
            color: var(--text-primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .form-control-modern::placeholder {
            color: var(--text-muted);
        }

        .form-select-modern {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-select-modern:focus {
            background: var(--bg-tertiary);
            border-color: var(--accent-primary);
            color: var(--text-primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        /* Switches */
        .form-check-modern {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border-color);
        }

        .form-check-modern:last-child {
            border-bottom: none;
        }

        .form-check-label-modern {
            color: var(--text-primary);
            font-weight: 500;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-check-label-modern i {
            color: var(--accent-primary);
            font-size: 0.9rem;
        }

        .form-switch .form-check-input {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            width: 3rem;
            height: 1.5rem;
            cursor: pointer;
        }

        .form-switch .form-check-input:checked {
            background: var(--gradient-primary);
            border-color: var(--accent-primary);
        }

        /* Buttons */
        .btn-modern {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            border: none;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }

        .btn-primary-modern {
            background: var(--gradient-primary);
            color: white;
        }

        .btn-primary-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }

        .btn-secondary-modern {
            background: transparent;
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
        }

        .btn-secondary-modern:hover {
            background: var(--bg-tertiary);
            border-color: var(--accent-primary);
            color: var(--accent-primary);
        }

        .btn-danger-modern {
            background: var(--accent-danger);
            color: white;
        }

        .btn-danger-modern:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }

        /* Alert Styles */
        .alert-modern {
            border-radius: 0.5rem;
            border: none;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .alert-success-modern {
            background: rgba(16, 185, 129, 0.1);
            color: var(--accent-success);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .alert-danger-modern {
            background: rgba(239, 68, 68, 0.1);
            color: var(--accent-danger);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        /* Security Cards */
        .security-card {
            background: rgba(59, 130, 246, 0.05);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .security-card:hover {
            background: rgba(59, 130, 246, 0.1);
            border-color: var(--accent-primary);
            transform: translateY(-2px);
        }

        .security-title {
            color: var(--accent-primary);
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .security-description {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        /* Activity Timeline */
        .activity-timeline {
            position: relative;
            padding-left: 2rem;
        }

        .activity-timeline::before {
            content: '';
            position: absolute;
            left: 0.5rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--border-color);
        }

        .activity-item {
            position: relative;
            padding-bottom: 1.5rem;
        }

        .activity-item::before {
            content: '';
            position: absolute;
            left: -1.5rem;
            top: 0.25rem;
            width: 0.75rem;
            height: 0.75rem;
            border-radius: 50%;
            background: var(--accent-primary);
            border: 2px solid var(--bg-card);
        }

        .activity-time {
            color: var(--text-muted);
            font-size: 0.8rem;
            margin-bottom: 0.25rem;
        }

        .activity-title {
            color: var(--text-primary);
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .activity-description {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .profile-sidebar {
                position: relative;
                top: 0;
                margin-bottom: 2rem;
            }

            .profile-tabs {
                flex-direction: column;
            }

            .profile-tab {
                border-bottom: 1px solid var(--border-color);
            }

            .profile-stats {
                grid-template-columns: repeat(3, 1fr);
            }

            .page-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Modern Navigation -->
    <?php include 'components/navigation.php'; ?>

    <!-- Main Container -->
    <div class="container-fluid px-4 profile-container">
        <!-- Success Alert -->
        <div id="successAlert" class="alert alert-modern alert-success-modern alert-dismissible fade show d-none" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <span id="successMessage"></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Profile Settings</h1>
            <p class="page-subtitle">Manage your personal information, preferences, and security settings</p>
        </div>

        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="profile-sidebar">
                    <div class="profile-avatar" onclick="changeAvatar()">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <h3 class="profile-name">John Doe</h3>
                    <p class="profile-email">john.doe@email.com</p>
                    
                    <div class="profile-badges">
                        <span class="profile-badge badge-verified">Verified</span>
                    </div>

                    <div class="profile-stats">
                        <div class="stat-item">
                            <div class="stat-value">18</div>
                            <div class="stat-label">Bookings</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">4.8</div>
                            <div class="stat-label">Rating</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">2Y</div>
                            <div class="stat-label">Joined</div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button class="btn-modern btn-primary-modern" onclick="viewDashboard()">
                            <i class="bi bi-speedometer2"></i>Dashboard
                        </button>
                        <button class="btn-modern btn-secondary-modern" onclick="viewBookings()">
                            <i class="bi bi-calendar-check"></i>My Bookings
                        </button>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-8">
                <div class="profile-main">
                    <!-- Tabs -->
                    <div class="profile-tabs">
                        <button class="profile-tab active" onclick="switchTab('personal')">
                            <i class="bi bi-person"></i>Personal Info
                        </button>
                        <button class="profile-tab" onclick="switchTab('preferences')">
                            <i class="bi bi-gear"></i>Preferences
                        </button>
                        <button class="profile-tab" onclick="switchTab('security')">
                            <i class="bi bi-shield-check"></i>Security
                        </button>
                        <button class="profile-tab" onclick="switchTab('activity')">
                            <i class="bi bi-clock-history"></i>Activity
                        </button>
                    </div>

                    <!-- Tab Content -->
                    <div class="tab-content">
                        <!-- Personal Info Tab -->
                        <div class="tab-pane active" id="personal-tab">
                            <form id="personalForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group-modern">
                                            <label class="form-label-modern">
                                                <i class="bi bi-person"></i>First Name
                                            </label>
                                            <input type="text" class="form-control-modern" id="firstName" value="John" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group-modern">
                                            <label class="form-label-modern">
                                                <i class="bi bi-person"></i>Last Name
                                            </label>
                                            <input type="text" class="form-control-modern" id="lastName" value="Doe" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group-modern">
                                    <label class="form-label-modern">
                                        <i class="bi bi-envelope"></i>Email Address
                                    </label>
                                    <input type="email" class="form-control-modern" id="email" value="john.doe@email.com" disabled>
                                </div>

                                <div class="form-group-modern">
                                    <label class="form-label-modern">
                                        <i class="bi bi-telephone"></i>Phone Number
                                    </label>
                                    <input type="tel" class="form-control-modern" id="phone" value="+1 (555) 123-4567">
                                </div>

                                <div class="form-group-modern">
                                    <label class="form-label-modern">
                                        <i class="bi bi-geo-alt"></i>Address
                                    </label>
                                    <input type="text" class="form-control-modern" id="address" value="123 Resort Lane, Paradise City">
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group-modern">
                                            <label class="form-label-modern">
                                                <i class="bi bi-calendar"></i>Date of Birth
                                            </label>
                                            <input type="date" class="form-control-modern" id="birthdate" value="1990-01-01">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group-modern">
                                            <label class="form-label-modern">
                                                <i class="bi bi-gender-ambiguous"></i>Gender
                                            </label>
                                            <select class="form-select-modern" id="gender">
                                                <option value="male" selected>Male</option>
                                                <option value="female">Female</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group-modern">
                                    <label class="form-label-modern">
                                        <i class="bi bi-file-text"></i>Bio
                                    </label>
                                    <textarea class="form-control-modern" id="bio" rows="3" placeholder="Tell us about yourself...">Adventure enthusiast and nature lover.</textarea>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="button" class="btn-modern btn-primary-modern" onclick="saveProfile()">
                                        <i class="bi bi-check-circle"></i>Save Changes
                                    </button>
                                    <button type="button" class="btn-modern btn-secondary-modern" onclick="resetProfile()">
                                        <i class="bi bi-arrow-clockwise"></i>Reset
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Preferences Tab -->
                        <div class="tab-pane" id="preferences-tab">
                            <h5 class="mb-4">Notification Preferences</h5>
                            
                            <div class="form-check-modern">
                                <label class="form-check-label-modern">
                                    <i class="bi bi-envelope"></i>Email Notifications
                                </label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                                </div>
                            </div>

                            <div class="form-check-modern">
                                <label class="form-check-label-modern">
                                    <i class="bi bi-phone"></i>SMS Notifications
                                </label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="smsNotifications">
                                </div>
                            </div>

                            <div class="form-check-modern">
                                <label class="form-check-label-modern">
                                    <i class="bi bi-newspaper"></i>Newsletter Subscription
                                </label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="newsletter" checked>
                                </div>
                            </div>

                            <div class="form-check-modern">
                                <label class="form-check-label-modern">
                                    <i class="bi bi-megaphone"></i>Marketing Emails
                                </label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="marketing">
                                </div>
                            </div>

                            <div class="form-check-modern">
                                <label class="form-check-label-modern">
                                    <i class="bi bi-eye"></i>Public Profile
                                </label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="publicProfile">
                                </div>
                            </div>

                            <div class="form-check-modern">
                                <label class="form-check-label-modern">
                                    <i class="bi bi-geo-alt"></i>Location Services
                                </label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="location">
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="button" class="btn-modern btn-primary-modern" onclick="savePreferences()">
                                    <i class="bi bi-check-circle"></i>Save Preferences
                                </button>
                            </div>
                        </div>

                        <!-- Security Tab -->
                        <div class="tab-pane" id="security-tab">
                            <h5 class="mb-4">Security Settings</h5>

                            <div class="security-card">
                                <div class="security-title">
                                    <i class="bi bi-lock"></i>Change Password
                                </div>
                                <div class="security-description">
                                    Update your password to keep your account secure
                                </div>
                                <button class="btn-modern btn-secondary-modern" onclick="changePassword()">
                                    <i class="bi bi-key"></i>Change Password
                                </button>
                            </div>

                            <div class="security-card">
                                <div class="security-title">
                                    <i class="bi bi-shield-check"></i>Two-Factor Authentication
                                </div>
                                <div class="security-description">
                                    Add an extra layer of security to your account
                                </div>
                                <button class="btn-modern btn-secondary-modern" onclick="enable2FA()">
                                    <i class="bi bi-phone"></i>Enable 2FA
                                </button>
                            </div>

                            <div class="security-card">
                                <div class="security-title">
                                    <i class="bi bi-clock-history"></i>Login History
                                </div>
                                <div class="security-description">
                                    View recent login attempts and locations
                                </div>
                                <button class="btn-modern btn-secondary-modern" onclick="viewLoginHistory()">
                                    <i class="bi bi-list-ul"></i>View History
                                </button>
                            </div>

                            <div class="security-card">
                                <div class="security-title">
                                    <i class="bi bi-phone"></i>Connected Devices
                                </div>
                                <div class="security-description">
                                    Manage devices that have access to your account
                                </div>
                                <button class="btn-modern btn-secondary-modern" onclick="manageDevices()">
                                    <i class="bi bi-gear"></i>Manage Devices
                                </button>
                            </div>

                            <div class="mt-4">
                                <button type="button" class="btn-modern btn-danger-modern" onclick="deleteAccount()">
                                    <i class="bi bi-trash"></i>Delete Account
                                </button>
                            </div>
                        </div>

                        <!-- Activity Tab -->
                        <div class="tab-pane" id="activity-tab">
                            <h5 class="mb-4">Recent Activity</h5>

                            <div class="activity-timeline">
                                <div class="activity-item">
                                    <div class="activity-time">2 hours ago</div>
                                    <div class="activity-title">Profile Updated</div>
                                    <div class="activity-description">Changed phone number and address</div>
                                </div>

                                <div class="activity-item">
                                    <div class="activity-time">1 day ago</div>
                                    <div class="activity-title">Booking Confirmed</div>
                                    <div class="activity-description">Basketball Court - March 25, 10:00 AM</div>
                                </div>

                                <div class="activity-item">
                                    <div class="activity-time">3 days ago</div>
                                    <div class="activity-title">Password Changed</div>
                                    <div class="activity-description">Security settings updated</div>
                                </div>

                                <div class="activity-item">
                                    <div class="activity-time">1 week ago</div>
                                    <div class="activity-title">New Booking</div>
                                    <div class="activity-description">Swimming Pool - March 26, 2:00 PM</div>
                                </div>

                                <div class="activity-item">
                                    <div class="activity-time">2 weeks ago</div>
                                    <div class="activity-title">Account Created</div>
                                    <div class="activity-description">Welcome to YP4K Resort</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Tab Switching
        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('active');
            });
            
            // Remove active class from all tab buttons
            document.querySelectorAll('.profile-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Add active class to clicked tab button
            event.target.classList.add('active');
        }

        // Profile Functions
        function saveProfile() {
            const firstName = document.getElementById('firstName').value;
            const lastName = document.getElementById('lastName').value;
            const phone = document.getElementById('phone').value;
            const address = document.getElementById('address').value;
            const birthdate = document.getElementById('birthdate').value;
            const gender = document.getElementById('gender').value;
            const bio = document.getElementById('bio').value;
            
            // Update sidebar display
            document.querySelector('.profile-name').textContent = firstName + ' ' + lastName;
            
            showSuccess('Profile updated successfully!');
        }

        function resetProfile() {
            if (confirm('Are you sure you want to reset all profile changes?')) {
                document.getElementById('personalForm').reset();
                showInfo('Profile reset to original values');
            }
        }

        function savePreferences() {
            const preferences = {
                emailNotifications: document.getElementById('emailNotifications').checked,
                smsNotifications: document.getElementById('smsNotifications').checked,
                newsletter: document.getElementById('newsletter').checked,
                marketing: document.getElementById('marketing').checked,
                publicProfile: document.getElementById('publicProfile').checked,
                location: document.getElementById('location').checked
            };
            
            showSuccess('Preferences saved successfully!');
        }

        function changePassword() {
            const newPassword = prompt('Enter new password:');
            if (newPassword) {
                const confirmPassword = prompt('Confirm new password:');
                if (newPassword === confirmPassword) {
                    showSuccess('Password changed successfully!');
                } else {
                    showError('Passwords do not match!');
                }
            }
        }

        function enable2FA() {
            showInfo('Two-factor authentication setup would open');
        }

        function viewLoginHistory() {
            showInfo('Login history would show recent login attempts');
        }

        function manageDevices() {
            showInfo('Connected devices management would open');
        }

        function deleteAccount() {
            const confirmation = prompt('Type "DELETE" to confirm account deletion:');
            if (confirmation === 'DELETE') {
                if (confirm('This action cannot be undone. Are you absolutely sure?')) {
                    showInfo('Account deletion would be processed');
                }
            } else {
                showInfo('Account deletion cancelled');
            }
        }

        function changeAvatar() {
            showInfo('Avatar upload functionality would open file picker');
        }

        function viewDashboard() {
            window.location.href = 'dashboard.php';
        }

        function viewBookings() {
            window.location.href = 'my-bookings.php';
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
            // Create error alert if it doesn't exist
            let errorAlert = document.getElementById('errorAlert');
            if (!errorAlert) {
                errorAlert = document.createElement('div');
                errorAlert.id = 'errorAlert';
                errorAlert.className = 'alert alert-modern alert-danger-modern alert-dismissible fade show';
                errorAlert.innerHTML = `
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <span id="errorMessage"></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.querySelector('.page-header').after(errorAlert);
            }
            
            document.getElementById('errorMessage').textContent = message;
            errorAlert.classList.remove('d-none');
            
            setTimeout(() => {
                errorAlert.classList.add('d-none');
            }, 5000);
        }

        function showInfo(message) {
            hideAllAlerts();
            // Create info alert if it doesn't exist
            let infoAlert = document.getElementById('infoAlert');
            if (!infoAlert) {
                infoAlert = document.createElement('div');
                infoAlert.id = 'infoAlert';
                infoAlert.className = 'alert alert-modern alert-info-modern alert-dismissible fade show';
                infoAlert.style.background = 'rgba(59, 130, 246, 0.1)';
                infoAlert.style.color = 'var(--accent-primary)';
                infoAlert.style.border = '1px solid rgba(59, 130, 246, 0.2)';
                infoAlert.innerHTML = `
                    <i class="bi bi-info-circle me-2"></i>
                    <span id="infoMessage"></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.querySelector('.page-header').after(infoAlert);
            }
            
            document.getElementById('infoMessage').textContent = message;
            infoAlert.classList.remove('d-none');
            
            setTimeout(() => {
                infoAlert.classList.add('d-none');
            }, 5000);
        }

        function hideAllAlerts() {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.classList.add('d-none');
            });
        }

        // Check for URL parameters
        window.addEventListener('load', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const success = urlParams.get('success');
            
            if (success) {
                showSuccess(decodeURIComponent(success));
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
