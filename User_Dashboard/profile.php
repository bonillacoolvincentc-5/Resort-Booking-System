<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../Authentication/login.php');
    exit();
}

require_once '../phpAuthentication/db_config.php';

$userId = $_SESSION['user_id'];

// Ensure profile columns exist in users table
$columnsToAdd = [
    'address' => "VARCHAR(255) DEFAULT NULL",
    'birthdate' => "DATE DEFAULT NULL",
    'gender' => "VARCHAR(50) DEFAULT NULL",
    'bio' => "TEXT DEFAULT NULL",
    'avatar' => "VARCHAR(255) DEFAULT NULL"
];
foreach ($columnsToAdd as $col => $def) {
    $check = $conn->query("SHOW COLUMNS FROM users LIKE '$col'");
    if ($check && $check->num_rows == 0) {
        $conn->query("ALTER TABLE users ADD COLUMN $col $def");
    }
}

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    session_destroy();
    header('Location: ../Authentication/login.php');
    exit();
}

// Convert any numerical PSGC codes in database row to character names
$rCode = $user['region'] ?? '';
$pCode = $user['province'] ?? '';
$cCode = $user['city_municipality'] ?? '';
$bCode = $user['barangay'] ?? '';
$updatedDb = false;

if (is_numeric($rCode) && strlen($rCode) >= 8) {
    $json = @file_get_contents("https://psgc.gitlab.io/api/regions/{$rCode}/");
    if ($json) { $d = json_decode($json, true); if (!empty($d['name'])) { $user['region'] = $d['name']; $updatedDb = true; } }
}
if (is_numeric($pCode) && strlen($pCode) >= 8) {
    $json = @file_get_contents("https://psgc.gitlab.io/api/provinces/{$pCode}/");
    if ($json) { $d = json_decode($json, true); if (!empty($d['name'])) { $user['province'] = $d['name']; $updatedDb = true; } }
}
if (is_numeric($cCode) && strlen($cCode) >= 8) {
    $json = @file_get_contents("https://psgc.gitlab.io/api/cities-municipalities/{$cCode}/");
    if ($json) { $d = json_decode($json, true); if (!empty($d['name'])) { $user['city_municipality'] = $d['name']; $updatedDb = true; } }
}
if (is_numeric($bCode) && strlen($bCode) >= 8) {
    $json = @file_get_contents("https://psgc.gitlab.io/api/barangays/{$bCode}/");
    if ($json) { $d = json_decode($json, true); if (!empty($d['name'])) { $user['barangay'] = $d['name']; $updatedDb = true; } }
}

if ($updatedDb) {
    $upStmt = $conn->prepare("UPDATE users SET region = ?, province = ?, city_municipality = ?, barangay = ? WHERE id = ?");
    if ($upStmt) {
        $upStmt->bind_param("ssssi", $user['region'], $user['province'], $user['city_municipality'], $user['barangay'], $userId);
        $upStmt->execute();
        $upStmt->close();
    }
}

// Construct address if address column is empty or contains numerical codes
$userAddress = $user['address'] ?? '';
if (empty($userAddress) || preg_match('/\d{8,}/', $userAddress)) {
    $parts = array_filter([
        $user['barangay'] ?? '',
        $user['city_municipality'] ?? '',
        $user['province'] ?? '',
        $user['region'] ?? ''
    ]);
    $userAddress = implode(', ', $parts);
}

// Full Name for display
$fullName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
if (empty($fullName)) {
    $fullName = $_SESSION['user_name'] ?? 'Guest User';
}
$userEmail = $user['email'] ?? ($_SESSION['user_email'] ?? '');
$userPhone = $user['phone_number'] ?? '';
$userBirthdate = $user['birthdate'] ?? '';
$userGender = $user['gender'] ?? '';
$userBio = $user['bio'] ?? '';

// Count user bookings
$bookingsCount = 0;
$stmtB = $conn->prepare("SELECT COUNT(*) as cnt FROM bookings WHERE email = ? OR full_name = ?");
$stmtB->bind_param("ss", $userEmail, $fullName);
$stmtB->execute();
$resB = $stmtB->get_result();
if ($rowB = $resB->fetch_assoc()) {
    $bookingsCount = $rowB['cnt'];
}
$stmtB->close();

// Calculate years or months joined
$joinedDate = new DateTime($user['created_at'] ?? 'now');
$now = new DateTime();
$diff = $now->diff($joinedDate);
if ($diff->y >= 1) {
    $joinedStr = $diff->y . 'Y';
} elseif ($diff->m >= 1) {
    $joinedStr = $diff->m . 'M';
} else {
    $joinedStr = 'New';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - YP4K Resort</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/resort-theme.css">
    <link rel="stylesheet" href="../css/dashboard-resort.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>

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
            background: rgba(157, 255, 0, 0.1);
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
            grid-template-columns: repeat(2, 1fr);
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
            background: rgba(16, 27, 23, 0.65);
            border-bottom: 1px solid rgba(157, 255, 0, 0.15);
            padding: 0 1.25rem;
            gap: 0.5rem;
        }

        .profile-tab {
            padding: 1.15rem 1.75rem;
            background: transparent;
            border: none;
            color: #cbd5e1;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            border-bottom: 3px solid transparent;
            margin-bottom: -1px;
        }

        .profile-tab i {
            font-size: 1.1rem;
            color: #cbd5e1;
            transition: color 0.3s ease;
        }

        .profile-tab:hover {
            color: #ffffff;
            background: rgba(157, 255, 0, 0.05);
        }

        .profile-tab:hover i {
            color: #9DFF00;
        }

        .profile-tab.active {
            color: #9DFF00;
            background: rgba(157, 255, 0, 0.08);
            border-bottom: 3px solid #9DFF00;
        }

        .profile-tab.active i {
            color: #9DFF00;
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
            width: 100%;
            display: block;
            background: rgba(16, 27, 23, 0.85);
            border: 1px solid rgba(157, 255, 0, 0.2);
            color: var(--text-primary);
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control-modern:focus {
            background: rgba(16, 27, 23, 0.95);
            border-color: #9DFF00;
            color: var(--text-primary);
            box-shadow: 0 0 0 3px rgba(157, 255, 0, 0.15);
            outline: none;
        }

        .form-control-modern::placeholder {
            color: var(--text-muted);
        }

        /* Make date and time picker icons white & easily visible on dark background */
        input[type="date"]::-webkit-calendar-picker-indicator,
        input[type="time"]::-webkit-calendar-picker-indicator,
        input[type="datetime-local"]::-webkit-calendar-picker-indicator,
        input[type="month"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
            opacity: 0.9;
            cursor: pointer;
            transition: opacity 0.2s ease, transform 0.2s ease;
        }
        input[type="date"]::-webkit-calendar-picker-indicator:hover,
        input[type="time"]::-webkit-calendar-picker-indicator:hover,
        input[type="datetime-local"]::-webkit-calendar-picker-indicator:hover,
        input[type="month"]::-webkit-calendar-picker-indicator:hover {
            opacity: 1;
            transform: scale(1.15);
        }

        .form-select-modern {
            width: 100%;
            display: block;
            background: rgba(16, 27, 23, 0.85);
            border: 1px solid rgba(157, 255, 0, 0.2);
            color: var(--text-primary);
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-select-modern:focus {
            background: rgba(16, 27, 23, 0.95);
            border-color: #9DFF00;
            color: var(--text-primary);
            box-shadow: 0 0 0 3px rgba(157, 255, 0, 0.15);
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
            color: #08120E !important;
            font-weight: 700;
        }

        .btn-primary-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(157, 255, 0, 0.25);
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
            background: rgba(157, 255, 0, 0.04);
            border: 1px solid rgba(157, 255, 0, 0.15);
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .security-card:hover {
            background: rgba(157, 255, 0, 0.08);
            border-color: #9DFF00;
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

        /* ── Responsive ─────────────────────────────────────────── */
        @media (max-width: 768px) {
            .profile-sidebar {
                position: relative;
                top: 0;
                margin-bottom: 2rem;
            }

            .profile-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .page-title {
                font-size: 1.5rem;
            }
        }

        /* ── Mobile-only form container refinements (≤ 767.98px) ── */
        @media (max-width: 767.98px) {

            /* ① Outer card: smooth rounded corners + glass-dark card look */
            .profile-main {
                background: rgba(10, 20, 16, 0.82);
                backdrop-filter: blur(20px) saturate(180%);
                -webkit-backdrop-filter: blur(20px) saturate(180%);
                border: 1px solid rgba(157, 255, 0, 0.14);
                border-radius: 1.25rem;          /* smooth, not boxy */
                overflow: hidden;                /* clip tabs to rounded corners */
                box-shadow:
                    0 8px 32px rgba(0, 0, 0, 0.45),
                    0 0 0 1px rgba(157, 255, 0, 0.04);
            }

            /* ② Neon accent bar along the top edge of the card */
            .profile-main::before {
                content: '';
                display: block;
                height: 2px;
                background: linear-gradient(
                    90deg,
                    transparent 0%,
                    rgba(157, 255, 0, 0.7) 40%,
                    rgba(157, 255, 0, 0.7) 60%,
                    transparent 100%
                );
            }

            /* ③ Mobile tab strip: horizontal scroll instead of vertical stack */
            .profile-tabs {
                flex-direction: row;           /* keep horizontal on mobile */
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: none;
                padding: 0 0.75rem;
                gap: 0.25rem;
                background: rgba(8, 16, 12, 0.6);
                border-bottom: 1px solid rgba(157, 255, 0, 0.12);
            }
            .profile-tabs::-webkit-scrollbar { display: none; }

            /* ④ Individual tabs: compact, pill-shaped, no pointer-overflow */
            .profile-tab {
                flex-shrink: 0;
                padding: 0.85rem 1.1rem;
                font-size: 0.85rem;
                border-bottom: 3px solid transparent;
                white-space: nowrap;
                border-radius: 0;
            }

            /* ⑤ Tab content area: generous inner breathing room */
            .tab-content {
                padding: 1.5rem 1.25rem;
            }

            /* ⑥ Form section header (h5 "Account Security") */
            .tab-content h5 {
                font-size: 1rem;
            }

            /* ⑦ Action buttons row: stack vertically on small screens */
            .tab-content .d-flex.gap-2 {
                flex-direction: column;
            }
            .tab-content .d-flex.gap-2 .btn-modern {
                width: 100%;
                justify-content: center;
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
            <p class="page-subtitle">Manage your personal information, password, and account activity</p>
        </div>

        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="profile-sidebar">
                    <div class="profile-avatar" onclick="changeAvatar()">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <h3 class="profile-name"><?php echo htmlspecialchars($fullName); ?></h3>
                    <p class="profile-email"><?php echo htmlspecialchars($userEmail); ?></p>

                    <div class="profile-stats">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $bookingsCount; ?></div>
                            <div class="stat-label">Bookings</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $joinedStr; ?></div>
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
                                            <input type="text" class="form-control-modern" id="firstName" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group-modern">
                                            <label class="form-label-modern">
                                                <i class="bi bi-person"></i>Last Name
                                            </label>
                                            <input type="text" class="form-control-modern" id="lastName" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group-modern">
                                            <label class="form-label-modern">
                                                <i class="bi bi-envelope"></i>Email Address
                                            </label>
                                            <input type="email" class="form-control-modern" id="email" value="<?php echo htmlspecialchars($userEmail); ?>" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group-modern">
                                            <label class="form-label-modern">
                                                <i class="bi bi-telephone"></i>Phone Number
                                            </label>
                                            <input type="tel" class="form-control-modern" id="phone" value="<?php echo htmlspecialchars($userPhone); ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group-modern">
                                    <label class="form-label-modern">
                                        <i class="bi bi-geo-alt"></i>Address
                                    </label>
                                    <input type="text" class="form-control-modern" id="address" value="<?php echo htmlspecialchars($userAddress); ?>">
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group-modern">
                                            <label class="form-label-modern">
                                                <i class="bi bi-calendar"></i>Date of Birth
                                            </label>
                                            <input type="date" class="form-control-modern" id="birthdate" value="<?php echo htmlspecialchars($userBirthdate); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group-modern">
                                            <label class="form-label-modern">
                                                <i class="bi bi-gender-ambiguous"></i>Gender
                                            </label>
                                            <select class="form-select-modern" id="gender">
                                                <option value="" <?php echo empty($userGender) ? 'selected' : ''; ?>>Select Gender</option>
                                                <option value="male" <?php echo strtolower($userGender) === 'male' ? 'selected' : ''; ?>>Male</option>
                                                <option value="female" <?php echo strtolower($userGender) === 'female' ? 'selected' : ''; ?>>Female</option>
                                                <option value="other" <?php echo strtolower($userGender) === 'other' ? 'selected' : ''; ?>>Other</option>
                                            </select>
                                        </div>
                                    </div>
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

                            <!-- Account Security / Change Password Section -->
                            <hr class="my-4" style="border-color: var(--border-color);">
                            <h5 class="mb-3"><i class="bi bi-shield-lock me-2"></i>Account Security</h5>
                            <div class="security-card mb-0">
                                <div class="security-title">
                                    <i class="bi bi-lock"></i>Password
                                </div>
                                <div class="security-description">
                                    Update your password to keep your account secure
                                </div>
                                <button type="button" class="btn-modern btn-secondary-modern" onclick="changePassword()">
                                    <i class="bi bi-key"></i>Change Password
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Unified Confirmation Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; background: #0f172a;">
                <div class="modal-header" style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <h5 class="modal-title" id="confirmModalTitle">
                        <i class="bi bi-question-circle me-2" style="color: #00d4ff;"></i>Confirmation
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <p id="confirmModalMessage" style="font-size: 1.1rem; color: #f8fafc; margin-bottom: 0;">Are you sure?</p>
                </div>
                <div class="modal-footer d-flex justify-content-center" style="border-top: none; padding-bottom: 1.5rem;">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" style="background: rgba(255,255,255,0.1); color: white; border: none;">Cancel</button>
                    <button type="button" class="btn px-4" id="confirmModalActionBtn" style="background: linear-gradient(135deg, #00d4ff 0%, #0077ff 100%); color: white; border: none;">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; background: #0f172a; color: white;">
                <div class="modal-header" style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <h5 class="modal-title">
                        <i class="bi bi-shield-lock me-2" style="color: #00d4ff;"></i>Change Password
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="changePasswordForm">
                        <div class="mb-3">
                            <label class="form-label text-white-50">Current Password</label>
                            <input type="password" class="form-control" id="cpCurrent" required style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: white;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white-50">New Password (Min 6 characters)</label>
                            <input type="password" class="form-control" id="cpNew" required minlength="6" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: white;">
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-white-50">Confirm New Password</label>
                            <input type="password" class="form-control" id="cpConfirm" required style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: white;">
                        </div>
                        <button type="submit" class="btn w-100" id="cpSubmitBtn" style="background: linear-gradient(135deg, #00d4ff 0%, #0077ff 100%); color: white; border: none; padding: 10px;">
                            Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showConfirmModal(title, message, confirmBtnText, confirmBtnColor, callback) {
            document.getElementById('confirmModalTitle').innerHTML = `<i class="bi bi-question-circle me-2" style="color: ${confirmBtnColor || '#00d4ff'};"></i>${title}`;
            document.getElementById('confirmModalMessage').textContent = message;
            
            const actionBtn = document.getElementById('confirmModalActionBtn');
            actionBtn.textContent = confirmBtnText;
            actionBtn.style.background = confirmBtnColor || 'linear-gradient(135deg, #00d4ff 0%, #0077ff 100%)';
            
            const newActionBtn = actionBtn.cloneNode(true);
            actionBtn.parentNode.replaceChild(newActionBtn, actionBtn);
            
            newActionBtn.addEventListener('click', () => {
                bootstrap.Modal.getInstance(document.getElementById('confirmModal')).hide();
                callback();
            });
            
            new bootstrap.Modal(document.getElementById('confirmModal')).show();
        }

        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('changePasswordForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const currentPassword = document.getElementById('cpCurrent').value;
                    const newPassword = document.getElementById('cpNew').value;
                    const confirmPassword = document.getElementById('cpConfirm').value;
                    const btn = document.getElementById('cpSubmitBtn');

                    if (newPassword.length < 6) {
                        showError('New password must be at least 6 characters!');
                        return;
                    }
                    if (newPassword !== confirmPassword) {
                        showError('Passwords do not match!');
                        return;
                    }

                    btn.disabled = true;
                    btn.textContent = 'Updating...';

                    const formData = new FormData();
                    formData.append('current_password', currentPassword);
                    formData.append('new_password', newPassword);

                    fetch('../php/change_password.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        btn.disabled = false;
                        btn.textContent = 'Update Password';
                        if (data.success) {
                            bootstrap.Modal.getInstance(document.getElementById('changePasswordModal')).hide();
                            showSuccess('Password updated successfully!');
                            form.reset();
                        } else {
                            showError(data.message || 'Failed to update password.');
                        }
                    })
                    .catch(err => {
                        btn.disabled = false;
                        btn.textContent = 'Update Password';
                        showError('Error connecting to server.');
                    });
                });
            }
        });
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

            const btn = document.querySelector('button[onclick="saveProfile()"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Saving...';
            btn.disabled = true;

            const formData = new FormData();
            formData.append('first_name', firstName);
            formData.append('last_name', lastName);
            formData.append('phone_number', phone);
            formData.append('address', address);
            formData.append('birthdate', birthdate);
            formData.append('gender', gender);
            formData.append('bio', bio);

            fetch('../php/update_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                if (data.success) {
                    document.querySelector('.profile-name').textContent = firstName + ' ' + lastName;
                    showSuccess('Profile updated successfully!');
                } else {
                    showError(data.message || 'Failed to update profile.');
                }
            })
            .catch(err => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                showError('Error connecting to server.');
            });
        }

        function resetProfile() {
            showConfirmModal('Reset Changes', 'Are you sure you want to reload and reset all changes?', 'Reset', '#ef4444', () => {
                window.location.reload();
            });
        }

        function changePassword() {
            document.getElementById('changePasswordForm').reset();
            new bootstrap.Modal(document.getElementById('changePasswordModal')).show();
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
                infoAlert.style.background = 'rgba(157, 255, 0, 0.1)';
                infoAlert.style.color = '#9DFF00';
                infoAlert.style.border = '1px solid rgba(157, 255, 0, 0.2)';
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

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
