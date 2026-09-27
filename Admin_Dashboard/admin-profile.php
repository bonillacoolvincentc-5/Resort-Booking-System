<?php
session_start();

// Include admin configuration
require_once 'config.php';

// Auth guard — same pattern as all other admin pages
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../Authentication/admin-login.php');
    exit();
}

// Include database configuration
admin_include('db_config.php');

// Grab current admin info from session
$adminName  = $_SESSION['admin_name']  ?? 'Administrator';
$adminEmail = $_SESSION['admin_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile — YP4K Resort</title>
    <meta name="description" content="Manage your admin profile, credentials, and notification preferences for YP4K Resort.">

    <!-- Core stylesheets — identical load order to every other admin page -->
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/resort-theme.css">
    <link rel="stylesheet" href="../css/dashboard-resort.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="components/sidebar.css">
    <link rel="stylesheet" href="components/navbar.css">
    <link rel="stylesheet" href="../css/admin-theme.css">
    <script src="components/admin-components.js" defer></script>

    <style>
        /* ═══════════════════════════════════════════════════════════════
           ADMIN PROFILE — Page-scoped styles
           All rules prefixed .profile-* or .modal-* to avoid global bleed.
        ═══════════════════════════════════════════════════════════════ */

        /* ── Page header ──────────────────────────────────────────── */
        .profile-page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .profile-page-header h1 {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--text);
            letter-spacing: -0.03em;
            margin: 0 0 0.25rem;
        }

        .profile-page-header p {
            color: var(--text-muted);
            font-size: 0.875rem;
            margin: 0;
        }

        /* ── Add New Admin button ─────────────────────────────────── */
        .btn-add-admin {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.65rem 1.25rem;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            color: #000;
            font-family: var(--font);
            font-weight: 700;
            font-size: 0.875rem;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: var(--t);
            box-shadow: 0 4px 18px rgba(157, 255, 0, 0.25);
            white-space: nowrap;
        }

        .btn-add-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(157, 255, 0, 0.42);
            color: #000;
        }

        .btn-add-admin:active { transform: translateY(0); }

        /* ── Profile layout grid ──────────────────────────────────── */
        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .profile-grid .span-full { grid-column: 1 / -1; }

        @media (max-width: 900px) {
            .profile-grid { grid-template-columns: 1fr; }
            .profile-grid .span-full { grid-column: 1; }
        }

        /* ── Shared profile card ──────────────────────────────────── */
        .profile-card {
            background: rgba(21, 34, 29, 0.75);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2rem;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .profile-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--accent), transparent);
            opacity: 0.5;
        }

        .profile-card:hover {
            border-color: rgba(157, 255, 0, 0.15);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.45);
        }

        /* ── Card heading ─────────────────────────────────────────── */
        .profile-card-title {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            font-size: 1rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 1.75rem;
            letter-spacing: -0.01em;
        }

        .profile-card-title .card-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(157, 255, 0, 0.1);
            color: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        /* ── Avatar section ───────────────────────────────────────── */
        .profile-avatar-section {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            margin-bottom: 1.75rem;
            padding-bottom: 1.75rem;
            border-bottom: 1px solid var(--border);
        }

        .profile-avatar-wrap {
            position: relative;
            flex-shrink: 0;
            cursor: pointer;
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(157, 255, 0, 0.15), rgba(110, 220, 69, 0.08));
            border: 2px solid rgba(157, 255, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--accent);
            overflow: hidden;
            transition: var(--t);
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-avatar-overlay {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.55);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: var(--t-fast);
        }

        .profile-avatar-wrap:hover .profile-avatar-overlay { opacity: 1; }
        .profile-avatar-wrap:hover .profile-avatar { border-color: var(--accent); }

        .profile-avatar-overlay i { color: #fff; font-size: 1.1rem; }

        .profile-avatar-meta h3 {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--text);
            margin: 0 0 0.2rem;
        }

        .profile-avatar-meta p {
            color: var(--text-muted);
            font-size: 0.8rem;
            margin: 0 0 0.65rem;
        }

        /* ── Change Photo button ──────────────────────────────────── */
        .btn-change-photo {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--accent);
            background: rgba(157, 255, 0, 0.08);
            border: 1px solid rgba(157, 255, 0, 0.22);
            border-radius: var(--radius-xs);
            padding: 0.35rem 0.75rem;
            cursor: pointer;
            transition: var(--t-fast);
            font-family: var(--font);
        }

        .btn-change-photo:hover {
            background: rgba(157, 255, 0, 0.16);
            border-color: rgba(157, 255, 0, 0.42);
            color: var(--accent);
        }

        #avatarFileInput { display: none; }

        /* ── Form field group ─────────────────────────────────────── */
        .profile-field { margin-bottom: 1.25rem; }

        .profile-label {
            display: block;
            color: var(--text-2);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            margin-bottom: 0.4rem;
        }

        /* ── Profile input ────────────────────────────────────────── */
        .profile-input {
            width: 100%;
            background: rgba(8, 18, 14, 0.7);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            color: var(--text);
            font-family: var(--font);
            font-size: 0.9rem;
            padding: 0.8rem 1rem;
            outline: none;
            transition: var(--t);
            display: block;
        }

        .profile-input::placeholder { color: var(--text-dim); }

        .profile-input:focus {
            border-color: rgba(157, 255, 0, 0.45);
            background: rgba(10, 22, 16, 0.85);
            box-shadow: 0 0 0 3px rgba(157, 255, 0, 0.07);
        }

        /* ── WebKit autofill fix (same technique as auth-resort.css) ─ */
        .profile-input:-webkit-autofill,
        .profile-input:-webkit-autofill:hover,
        .profile-input:-webkit-autofill:focus,
        .profile-input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 1000px rgba(8, 18, 14, 1) inset;
            box-shadow:         0 0 0 1000px rgba(8, 18, 14, 1) inset;
            -webkit-text-fill-color: var(--text, #f0fff4);
            border-color: rgba(157, 255, 0, 0.45);
            border-radius: var(--radius-sm);
            transition: background-color 9999s ease 0s, color 9999s ease 0s;
        }

        /* ── Password input group (eye toggle) ───────────────────── */
        .profile-input-group { position: relative; }
        .profile-input-group .profile-input { padding-right: 3rem; }

        .profile-eye-btn {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 1rem;
            transition: var(--t-fast);
            padding: 0.2rem;
        }

        .profile-eye-btn:hover { color: var(--text-2); }

        /* ── Form action row ──────────────────────────────────────── */
        .profile-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 1.75rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border);
        }

        /* Primary green button */
        .btn-profile-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            color: #000;
            font-family: var(--font);
            font-weight: 700;
            font-size: 0.875rem;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: var(--t);
            box-shadow: 0 4px 18px rgba(157, 255, 0, 0.22);
        }

        .btn-profile-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(157, 255, 0, 0.38);
        }

        .btn-profile-primary:active { transform: translateY(0); }

        .btn-profile-primary:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            transform: none;
        }

        /* Muted outline cancel button */
        .btn-profile-cancel {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            background: transparent;
            color: var(--text-muted);
            font-family: var(--font);
            font-weight: 600;
            font-size: 0.875rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: var(--t-fast);
        }

        .btn-profile-cancel:hover {
            border-color: rgba(255, 255, 255, 0.18);
            color: var(--text);
            background: rgba(255, 255, 255, 0.04);
        }

        /* ── Toggle switch ────────────────────────────────────────── */
        .toggle-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.1rem 0;
            border-bottom: 1px solid var(--border-2);
        }

        .toggle-row:last-of-type { border-bottom: none; }

        .toggle-info h4 {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text);
            margin: 0 0 0.25rem;
        }

        .toggle-info p {
            font-size: 0.78rem;
            color: var(--text-muted);
            margin: 0;
            line-height: 1.4;
        }

        /* iOS-style toggle */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 48px;
            height: 26px;
            flex-shrink: 0;
        }

        .toggle-switch input { opacity: 0; width: 0; height: 0; }

        .toggle-slider {
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid var(--border);
            border-radius: 26px;
            cursor: pointer;
            transition: var(--t);
        }

        .toggle-slider::before {
            content: '';
            position: absolute;
            width: 18px;
            height: 18px;
            left: 3px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--text-dim);
            border-radius: 50%;
            transition: var(--t);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
        }

        .toggle-switch input:checked + .toggle-slider {
            background: rgba(157, 255, 0, 0.2);
            border-color: rgba(157, 255, 0, 0.5);
            box-shadow: 0 0 12px rgba(157, 255, 0, 0.15);
        }

        .toggle-switch input:checked + .toggle-slider::before {
            transform: translateX(22px) translateY(-50%);
            background: var(--accent);
        }

        /* ── Toast notification ───────────────────────────────────── */
        .profile-toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.9rem 1.25rem;
            background: rgba(21, 34, 29, 0.97);
            border: 1px solid rgba(157, 255, 0, 0.3);
            border-radius: var(--radius-sm);
            color: var(--text);
            font-size: 0.875rem;
            font-weight: 500;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.6);
            z-index: 9999;
            backdrop-filter: blur(20px);
            transform: translateY(100px);
            opacity: 0;
            transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                        opacity 0.35s ease;
            pointer-events: none;
            max-width: 340px;
        }

        .profile-toast.show {
            transform: translateY(0);
            opacity: 1;
            pointer-events: auto;
        }

        .profile-toast .toast-icon {
            color: var(--accent);
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .profile-toast.toast-error { border-color: rgba(255, 90, 95, 0.35); }
        .profile-toast.toast-error .toast-icon { color: var(--error); }

        /* ── Add Admin Modal ──────────────────────────────────────── */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(5, 10, 8, 0.75);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            z-index: 1050;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .modal-overlay.open {
            opacity: 1;
            pointer-events: auto;
        }

        .modal-box {
            background: rgba(18, 30, 24, 0.98);
            border: 1px solid rgba(157, 255, 0, 0.15);
            border-radius: var(--radius-xl);
            padding: 2rem;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.7),
                        0 0 50px rgba(157, 255, 0, 0.05);
            position: relative;
            transform: translateY(20px) scale(0.97);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .modal-box::before {
            content: '';
            position: absolute;
            top: 0; left: 20%; right: 20%;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--accent), transparent);
        }

        .modal-overlay.open .modal-box {
            transform: translateY(0) scale(1);
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .modal-header h3 {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 1rem;
            font-weight: 700;
            color: var(--text);
            margin: 0;
        }

        .modal-header h3 i { color: var(--accent); }

        .modal-close-btn {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 1.15rem;
            transition: var(--t-fast);
            padding: 0.2rem 0.4rem;
            border-radius: var(--radius-xs);
        }

        .modal-close-btn:hover {
            color: var(--text);
            background: rgba(255, 255, 255, 0.06);
        }

        .modal-field { margin-bottom: 1.1rem; }

        .modal-input {
            width: 100%;
            background: rgba(8, 18, 14, 0.7);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            color: var(--text);
            font-family: var(--font);
            font-size: 0.9rem;
            padding: 0.78rem 1rem;
            outline: none;
            transition: var(--t);
            display: block;
        }

        .modal-input::placeholder { color: var(--text-dim); }

        .modal-input:focus {
            border-color: rgba(157, 255, 0, 0.45);
            background: rgba(10, 22, 16, 0.9);
            box-shadow: 0 0 0 3px rgba(157, 255, 0, 0.07);
        }

        /* WebKit autofill fix for modal inputs */
        .modal-input:-webkit-autofill,
        .modal-input:-webkit-autofill:hover,
        .modal-input:-webkit-autofill:focus,
        .modal-input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 1000px rgba(8, 18, 14, 1) inset;
            box-shadow:         0 0 0 1000px rgba(8, 18, 14, 1) inset;
            -webkit-text-fill-color: var(--text, #f0fff4);
            border-color: rgba(157, 255, 0, 0.45);
            border-radius: var(--radius-sm);
            transition: background-color 9999s ease 0s, color 9999s ease 0s;
        }

        .modal-input-pw { padding-right: 3rem !important; }

        .modal-footer {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
            margin-top: 1.5rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--border);
        }

        /* ── Password strength bar ────────────────────────────────── */
        .pw-strength-bar {
            margin-top: 0.5rem;
            height: 4px;
            border-radius: 4px;
            background: var(--border);
            overflow: hidden;
        }

        .pw-strength-fill {
            height: 100%;
            border-radius: 4px;
            width: 0%;
            transition: width 0.4s ease, background-color 0.4s ease;
        }

        .pw-strength-label {
            font-size: 0.72rem;
            color: var(--text-dim);
            margin-top: 0.3rem;
        }

        /* ── Responsive tweaks ────────────────────────────────────── */
        @media (max-width: 600px) {
            .profile-avatar-section { flex-direction: column; text-align: center; }
            .profile-actions { flex-direction: column; }
            .btn-profile-primary,
            .btn-profile-cancel { width: 100%; justify-content: center; }
            .profile-page-header { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>

    <!-- Sidebar + Navbar injected by admin-components.js (same as all pages) -->
    <div id="sidebar-container"></div>
    <div id="navbar-container"></div>

    <!-- ─── Main Content ───────────────────────────────────────────── -->
    <div id="mainContent" class="main-content">

        <!-- Page Header -->
        <div class="profile-page-header">
            <div>
                <h1>
                    <i class="bi bi-person-circle me-2"
                       style="color:var(--accent);font-size:1.5rem;vertical-align:middle;"></i>
                    Admin Profile
                </h1>
                <p>Manage your personal details, credentials, and notification preferences.</p>
            </div>
            <button class="btn-add-admin" id="btnOpenAddAdmin" onclick="openAddAdminModal()">
                <i class="bi bi-person-plus-fill"></i>
                + Add New Admin
            </button>
        </div>

        <!-- ── Profile Grid ─────────────────────────────────────────── -->
        <div class="profile-grid">

            <!-- ─── ① Personal Details Card ─────────────────────────── -->
            <div class="profile-card">
                <div class="profile-card-title">
                    <div class="card-icon"><i class="bi bi-person-fill"></i></div>
                    Personal Details
                </div>

                <!-- Avatar + name -->
                <div class="profile-avatar-section">
                    <div class="profile-avatar-wrap"
                         onclick="document.getElementById('avatarFileInput').click()"
                         title="Change profile photo">
                        <div class="profile-avatar" id="profileAvatarPreview">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <div class="profile-avatar-overlay">
                            <i class="bi bi-camera-fill"></i>
                        </div>
                    </div>
                    <div class="profile-avatar-meta">
                        <h3 id="avatarName"><?= htmlspecialchars($adminName) ?></h3>
                        <p>System Administrator</p>
                        <button class="btn-change-photo"
                                onclick="document.getElementById('avatarFileInput').click()"
                                type="button">
                            <i class="bi bi-upload"></i> Change Photo
                        </button>
                        <input type="file" id="avatarFileInput" accept="image/*"
                               onchange="handleAvatarChange(event)">
                    </div>
                </div>

                <!-- Personal details form -->
                <form id="personalDetailsForm" onsubmit="savePersonalDetails(event)">
                    <div class="profile-field">
                        <label class="profile-label" for="profileFullName">Full Name</label>
                        <input class="profile-input"
                               type="text"
                               id="profileFullName"
                               name="full_name"
                               placeholder="Enter your full name"
                               value="<?= htmlspecialchars($adminName) ?>"
                               autocomplete="name"
                               required>
                    </div>

                    <div class="profile-field">
                        <label class="profile-label" for="profileEmail">Admin Email</label>
                        <input class="profile-input"
                               type="email"
                               id="profileEmail"
                               name="email"
                               placeholder="Enter your email address"
                               value="<?= htmlspecialchars($adminEmail) ?>"
                               autocomplete="email"
                               required>
                    </div>

                    <div class="profile-field">
                        <label class="profile-label" for="profileContact">Contact Number</label>
                        <input class="profile-input"
                               type="tel"
                               id="profileContact"
                               name="contact"
                               placeholder="+63 9XX XXX XXXX"
                               autocomplete="tel">
                    </div>

                    <div class="profile-actions">
                        <button type="submit" class="btn-profile-primary" id="btnSavePersonal">
                            <i class="bi bi-check2-circle"></i> Save Changes
                        </button>
                        <button type="button" class="btn-profile-cancel" onclick="resetPersonalForm()">
                            <i class="bi bi-x-circle"></i> Cancel
                        </button>
                    </div>
                </form>
            </div><!-- /.profile-card personal -->

            <!-- ─── ② Change Password Card ──────────────────────────── -->
            <div class="profile-card">
                <div class="profile-card-title">
                    <div class="card-icon"><i class="bi bi-shield-lock-fill"></i></div>
                    Change Password
                </div>

                <form id="changePasswordForm" onsubmit="savePassword(event)">
                    <div class="profile-field">
                        <label class="profile-label" for="currentPassword">Current Password</label>
                        <div class="profile-input-group">
                            <input class="profile-input"
                                   type="password"
                                   id="currentPassword"
                                   name="current_password"
                                   placeholder="Enter current password"
                                   autocomplete="current-password"
                                   required>
                            <button type="button" class="profile-eye-btn"
                                    onclick="togglePw('currentPassword', this)"
                                    aria-label="Show or hide current password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="profile-field">
                        <label class="profile-label" for="newPassword">New Password</label>
                        <div class="profile-input-group">
                            <input class="profile-input"
                                   type="password"
                                   id="newPassword"
                                   name="new_password"
                                   placeholder="Enter new password"
                                   autocomplete="new-password"
                                   oninput="updateStrength(this.value)"
                                   required>
                            <button type="button" class="profile-eye-btn"
                                    onclick="togglePw('newPassword', this)"
                                    aria-label="Show or hide new password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="pw-strength-bar">
                            <div class="pw-strength-fill" id="pwStrengthFill"></div>
                        </div>
                        <div class="pw-strength-label" id="pwStrengthLabel">Enter a password</div>
                    </div>

                    <div class="profile-field">
                        <label class="profile-label" for="confirmPassword">Confirm New Password</label>
                        <div class="profile-input-group">
                            <input class="profile-input"
                                   type="password"
                                   id="confirmPassword"
                                   name="confirm_password"
                                   placeholder="Re-enter new password"
                                   autocomplete="new-password"
                                   required>
                            <button type="button" class="profile-eye-btn"
                                    onclick="togglePw('confirmPassword', this)"
                                    aria-label="Show or hide confirm password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="profile-actions">
                        <button type="submit" class="btn-profile-primary" id="btnUpdatePassword">
                            <i class="bi bi-lock-fill"></i> Update Password
                        </button>
                    </div>
                </form>
            </div><!-- /.profile-card password -->



        </div><!-- /.profile-grid -->
    </div><!-- /.main-content -->

    <!-- ═══════════════════════════════════════════════════════════════
         Add New Admin Modal
    ═══════════════════════════════════════════════════════════════ -->
    <div class="modal-overlay"
         id="addAdminModal"
         role="dialog"
         aria-modal="true"
         aria-labelledby="addAdminModalTitle"
         onclick="closeModalOnBackdrop(event)">
        <div class="modal-box">
            <div class="modal-header">
                <h3 id="addAdminModalTitle">
                    <i class="bi bi-person-plus-fill"></i> Add New Admin
                </h3>
                <button class="modal-close-btn"
                        onclick="closeAddAdminModal()"
                        aria-label="Close modal">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <form id="addAdminForm" onsubmit="submitNewAdmin(event)">
                <div class="modal-field">
                    <label class="profile-label" for="newAdminName">Full Name</label>
                    <input class="modal-input"
                           type="text"
                           id="newAdminName"
                           name="admin_name"
                           placeholder="Enter full name"
                           autocomplete="off"
                           required>
                </div>

                <div class="modal-field">
                    <label class="profile-label" for="newAdminEmail">Email Address</label>
                    <input class="modal-input"
                           type="email"
                           id="newAdminEmail"
                           name="admin_email"
                           placeholder="admin@example.com"
                           autocomplete="off"
                           required>
                </div>

                <div class="modal-field">
                    <label class="profile-label" for="newAdminTempPw">Temporary Password</label>
                    <div class="profile-input-group">
                        <input class="modal-input modal-input-pw"
                               type="password"
                               id="newAdminTempPw"
                               name="temp_password"
                               placeholder="Set a temporary password"
                               autocomplete="new-password"
                               required>
                        <button type="button"
                                class="profile-eye-btn"
                                onclick="togglePw('newAdminTempPw', this)"
                                aria-label="Show or hide temporary password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div id="admin-password-checker" style="font-size: 0.72rem; margin-top: 0.6rem; display: flex; flex-wrap: wrap; gap: 0.4rem;">
                        <div id="admin-req-length" style="color: rgba(255,255,255,0.5); background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.05); padding: 0.2rem 0.6rem; border-radius: 12px; display: flex; align-items: center; gap: 0.25rem; transition: all 0.3s ease;"><i class="bi bi-circle"></i> 8+ characters</div>
                        <div id="admin-req-capital" style="color: rgba(255,255,255,0.5); background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.05); padding: 0.2rem 0.6rem; border-radius: 12px; display: flex; align-items: center; gap: 0.25rem; transition: all 0.3s ease;"><i class="bi bi-circle"></i> 1 capital letter</div>
                        <div id="admin-req-number" style="color: rgba(255,255,255,0.5); background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.05); padding: 0.2rem 0.6rem; border-radius: 12px; display: flex; align-items: center; gap: 0.25rem; transition: all 0.3s ease;"><i class="bi bi-circle"></i> 1 number</div>
                        <div id="admin-req-special" style="color: rgba(255,255,255,0.5); background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.05); padding: 0.2rem 0.6rem; border-radius: 12px; display: flex; align-items: center; gap: 0.25rem; transition: all 0.3s ease;"><i class="bi bi-circle"></i> 1 special char</div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn-profile-cancel"
                            onclick="closeAddAdminModal()">
                        Cancel
                    </button>
                    <button type="submit" class="btn-profile-primary" id="btnCreateAdmin">
                        <i class="bi bi-person-check-fill"></i> Create Admin
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Toast notification ──────────────────────────────────────── -->
    <div class="profile-toast" id="profileToast" role="alert" aria-live="polite">
        <i class="bi bi-check-circle-fill toast-icon" id="toastIcon"></i>
        <span id="toastMessage">Changes saved successfully.</span>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>

    <script>
    /* ═══════════════════════════════════════════════════════════════════
       ADMIN PROFILE — Page JS
       Self-contained. Does NOT modify admin-components.js or shared logic.
    ═══════════════════════════════════════════════════════════════════ */

    // ── Toast ────────────────────────────────────────────────────────
    let _toastTimer;
    function showToast(message, isError = false) {
        const toast  = document.getElementById('profileToast');
        const icon   = document.getElementById('toastIcon');
        const msgEl  = document.getElementById('toastMessage');

        msgEl.textContent = message;
        toast.classList.toggle('toast-error', isError);
        icon.className = isError
            ? 'bi bi-exclamation-circle-fill toast-icon'
            : 'bi bi-check-circle-fill toast-icon';

        toast.classList.add('show');
        clearTimeout(_toastTimer);
        _toastTimer = setTimeout(() => toast.classList.remove('show'), 3500);
    }

    // ── Avatar preview ───────────────────────────────────────────────
    function handleAvatarChange(event) {
        const file = event.target.files[0];
        if (!file) return;
        if (!file.type.startsWith('image/')) {
            showToast('Please select a valid image file.', true);
            return;
        }
        const reader = new FileReader();
        reader.onload = (e) => {
            const preview = document.getElementById('profileAvatarPreview');
            preview.innerHTML = `<img src="${e.target.result}" alt="Profile photo">`;
        };
        reader.readAsDataURL(file);
        showToast('Photo updated — remember to save your changes.');
    }

    // ── Personal details ─────────────────────────────────────────────
    function savePersonalDetails(event) {
        event.preventDefault();
        const btn = document.getElementById('btnSavePersonal');
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving&hellip;';

        // TODO: replace with fetch() to your profile update endpoint
        setTimeout(() => {
            const nameVal = document.getElementById('profileFullName').value.trim();
            document.getElementById('avatarName').textContent = nameVal;
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check2-circle"></i> Save Changes';
            showToast('Personal details saved successfully.');
        }, 800);
    }

    function resetPersonalForm() {
        document.getElementById('personalDetailsForm').reset();
        showToast('Changes discarded.');
    }

    // ── Password toggle ──────────────────────────────────────────────
    function togglePw(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon  = btn.querySelector('i');
        if (input.type === 'password') {
            input.type    = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            input.type    = 'password';
            icon.className = 'bi bi-eye';
        }
    }

    // ── Password strength ────────────────────────────────────────────
    function updateStrength(value) {
        const fill  = document.getElementById('pwStrengthFill');
        const label = document.getElementById('pwStrengthLabel');
        let score   = 0;
        if (value.length >= 8)           score++;
        if (/[A-Z]/.test(value))         score++;
        if (/[0-9]/.test(value))         score++;
        if (/[^A-Za-z0-9]/.test(value)) score++;

        const levels = [
            { pct: '0%',   color: 'transparent', text: 'Enter a password' },
            { pct: '25%',  color: '#FF5A5F',      text: 'Weak'  },
            { pct: '50%',  color: '#F7B731',      text: 'Fair'  },
            { pct: '75%',  color: '#6EDC45',      text: 'Good'  },
            { pct: '100%', color: '#9DFF00',      text: 'Strong'},
        ];
        const lvl = value.length === 0 ? levels[0] : (levels[score] || levels[1]);
        fill.style.width           = lvl.pct;
        fill.style.backgroundColor = lvl.color;
        label.textContent          = lvl.text;
    }

    // ── Change password ──────────────────────────────────────────────
    function savePassword(event) {
        event.preventDefault();
        const newPw  = document.getElementById('newPassword').value;
        const confPw = document.getElementById('confirmPassword').value;

        if (newPw !== confPw) {
            showToast('New passwords do not match.', true);
            return;
        }
        if (newPw.length < 8) {
            showToast('Password must be at least 8 characters.', true);
            return;
        }

        const btn = document.getElementById('btnUpdatePassword');
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Updating&hellip;';

        // TODO: replace with fetch() to your password update endpoint
        setTimeout(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-lock-fill"></i> Update Password';
            document.getElementById('changePasswordForm').reset();
            updateStrength('');
            showToast('Password updated successfully.');
        }, 900);
    }

    // ── Alert preferences ────────────────────────────────────────────
    function savePreferences(event) {
        event.preventDefault();
        const btn = document.getElementById('btnSavePrefs');
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving&hellip;';

        const prefs = {
            notify_booking: document.getElementById('toggleNewBooking').checked,
            notify_cancel:  document.getElementById('toggleCancelAlert').checked,
        };

        // Persist to localStorage until backend handler is ready
        localStorage.setItem('admin_alert_prefs', JSON.stringify(prefs));

        setTimeout(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-floppy-fill"></i> Save Preferences';
            showToast('Alert preferences saved.');
        }, 600);
    }

    // Restore toggle state on page load
    (function restorePreferences() {
        const saved = localStorage.getItem('admin_alert_prefs');
        if (!saved) return;
        try {
            const prefs = JSON.parse(saved);
            if (typeof prefs.notify_booking !== 'undefined')
                document.getElementById('toggleNewBooking').checked = !!prefs.notify_booking;
            if (typeof prefs.notify_cancel !== 'undefined')
                document.getElementById('toggleCancelAlert').checked = !!prefs.notify_cancel;
        } catch (_) { /* ignore corrupt data */ }
    })();

    // ── Add New Admin Modal ──────────────────────────────────────────
    function openAddAdminModal() {
        document.getElementById('addAdminModal').classList.add('open');
        document.body.style.overflow = 'hidden';
        setTimeout(() => {
            const f = document.getElementById('newAdminName');
            if (f) f.focus();
        }, 300);
    }

    function closeAddAdminModal() {
        document.getElementById('addAdminModal').classList.remove('open');
        document.body.style.overflow = '';
        document.getElementById('addAdminForm').reset();
        resetAdminPasswordChecker();
    }

    function resetAdminPasswordChecker() {
        const reqs = [
            { id: 'admin-req-length', text: '8+ characters' },
            { id: 'admin-req-capital', text: '1 capital letter' },
            { id: 'admin-req-number', text: '1 number' },
            { id: 'admin-req-special', text: '1 special char' }
        ];
        reqs.forEach(r => {
            const el = document.getElementById(r.id);
            if (el) {
                el.style.color = 'rgba(255,255,255,0.5)';
                el.style.background = 'rgba(255,255,255,0.05)';
                el.style.borderColor = 'rgba(255,255,255,0.05)';
                el.innerHTML = '<i class="bi bi-circle"></i> ' + r.text;
            }
        });
    }

    function setupAdminPasswordChecker() {
        const passInput = document.getElementById('newAdminTempPw');
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

            updateReq('admin-req-length', p.length >= 8, '8+ characters');
            updateReq('admin-req-capital', /[A-Z]/.test(p), '1 capital letter');
            updateReq('admin-req-number', /\d/.test(p), '1 number');
            updateReq('admin-req-special', /[!@#$%^&*(),.?":{}|<>]/.test(p), '1 special char');
        });
    }
    document.addEventListener('DOMContentLoaded', setupAdminPasswordChecker);

    function closeModalOnBackdrop(event) {
        if (event.target === document.getElementById('addAdminModal')) {
            closeAddAdminModal();
        }
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeAddAdminModal();
    });

    function submitNewAdmin(event) {
        event.preventDefault();
        const pass = document.getElementById('newAdminTempPw').value;
        if (pass.length < 8 || !/[A-Z]/.test(pass) || !/\d/.test(pass) || !/[!@#$%^&*(),.?":{}|<>]/.test(pass)) {
            showToast('Password does not meet all requirements (min 8 chars, 1 uppercase, 1 number, 1 special character).', 'error');
            return;
        }

        const btn = document.getElementById('btnCreateAdmin');
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Creating&hellip;';

        const name = document.getElementById('newAdminName').value.trim();

        // TODO: replace with fetch() to ../php/admin_register_process.php
        setTimeout(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-person-check-fill"></i> Create Admin';
            closeAddAdminModal();
            showToast(`Admin account for ${name} created successfully.`);
        }, 900);
    }
    </script>
</body>
</html>
