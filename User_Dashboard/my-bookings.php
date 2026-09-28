<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../Authentication/login.php');
    exit();
}

$lastVisitedBookings = $_SESSION['last_visited_bookings'] ?? '2000-01-01 00:00:00';
$_SESSION['last_visited_bookings'] = date('Y-m-d H:i:s');

require_once '../phpAuthentication/db_config.php';
require_once __DIR__ . '/../php/check_expired_bookings.php';

/** @var mysqli $conn */
global $conn;

// Get user info
$userId    = $_SESSION['user_id'];
$userName  = $_SESSION['user_name'] ?? '';
$userEmail = $_SESSION['user_email'] ?? '';

if ((empty($userName) || empty($userEmail)) && $userId) {
    $stmtU = $conn->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
    $stmtU->bind_param("i", $userId);
    $stmtU->execute();
    $rowU = $stmtU->get_result()->fetch_assoc();
    if ($rowU) {
        if (empty($userName)) {
            $userName = trim(($rowU['first_name'] ?? '') . ' ' . ($rowU['last_name'] ?? ''));
            $_SESSION['user_name'] = $userName;
        }
        if (empty($userEmail)) {
            $userEmail = $rowU['email'] ?? '';
            $_SESSION['user_email'] = $userEmail;
        }
    }
    $stmtU->close();
}

// Get user bookings from database
$stmt = $conn->prepare("
    SELECT 
        b.id,
        b.booking_reference,
        b.booking_type,
        b.activity_id,
        b.accommodation_id,
        b.bundle_id,
        b.full_name,
        b.email,
        b.phone_number,
        b.check_in_date,
        b.check_in_time,
        b.check_out_date,
        b.check_out_time,
        b.number_of_days,
        b.number_of_guests,
        b.special_requests,
        b.total_amount,
        b.payment_status,
        b.payment_proof,
        b.expires_at,
        TIMESTAMPDIFF(SECOND, NOW(), b.expires_at) as seconds_remaining,
        b.created_at,
        bs.name as status_name,
        bs.description as status_description,
        bs.color as status_color,
        CASE 
            WHEN b.booking_type = 'activity' THEN a.name
            WHEN b.booking_type = 'bundle' THEN bun.name
            ELSE acc.name
        END as item_name,
        CASE 
            WHEN b.booking_type = 'activity' THEN 'bi-activity'
            WHEN b.booking_type = 'bundle' THEN 'bi-box-seam'
            ELSE 'bi-house-door'
        END as item_icon,
        bun.description as bundle_description
    FROM bookings b
    LEFT JOIN booking_status bs ON b.status_id = bs.id
    LEFT JOIN activities a ON b.activity_id = a.id
    LEFT JOIN accommodations acc ON b.accommodation_id = acc.id
    LEFT JOIN bundles bun ON b.bundle_id = bun.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

$bookings = [];
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}

// Get booking counts by status
$statsQuery = "
    SELECT 
        bs.name as status,
        COUNT(*) as count
    FROM bookings b
    JOIN booking_status bs ON b.status_id = bs.id
    WHERE b.user_id = ?
    GROUP BY bs.name
";

$stmt = $conn->prepare($statsQuery);
$stmt->bind_param('i', $userId);
$stmt->execute();
$statsResult = $stmt->get_result();

$stats = [
    'pending_review' => 0,
    'approved' => 0,
    'rejected' => 0,
    'completed' => 0,
    'cancelled' => 0
];

while ($row = $statsResult->fetch_assoc()) {
    $stats[$row['status']] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - YP4K Resort</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/resort-theme.css">
    <link rel="stylesheet" href="../css/dashboard-resort.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            color-scheme: dark;
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --bg-tertiary: #334155;
            --bg-card: rgba(30, 41, 59, 0.8);
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --text-muted: #cbd5e1;
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
        .bookings-container {
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

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--bg-card);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--gradient-primary);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
        }

        .stat-icon.primary {
            background: rgba(59, 130, 246, 0.2);
            color: var(--accent-primary);
        }

        .stat-icon.success {
            background: rgba(16, 185, 129, 0.2);
            color: var(--accent-success);
        }

        .stat-icon.warning {
            background: rgba(245, 158, 11, 0.2);
            color: var(--accent-warning);
        }

        .stat-icon.danger {
            background: rgba(239, 68, 68, 0.2);
            color: var(--accent-danger);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--text-primary), var(--text-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Search Bar */
        .search-bar-container {
            background: var(--bg-card);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 1.25rem;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }
        .search-bar-container:focus-within {
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .search-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .search-icon {
            position: absolute;
            left: 1.25rem;
            color: var(--text-secondary);
            font-size: 1.2rem;
        }
        .premium-search-input {
            width: 100%;
            background: var(--bg-tertiary);
            border: 1px solid transparent;
            color: var(--text-primary);
            padding: 1rem 1rem 1rem 3.25rem;
            border-radius: 0.75rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .premium-search-input:focus {
            outline: none;
            background: rgba(30, 41, 59, 0.9);
        }
        .premium-search-input::placeholder {
            color: rgba(203, 213, 225, 0.5);
        }

        /* Booking Cards */
        .booking-card {
            background: var(--bg-card);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            overflow: hidden;
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }

        .booking-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-xl);
            border-color: var(--accent-primary);
        }

        .booking-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: between;
            align-items: center;
        }

        .booking-id {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.9rem;
        }

        .booking-status {
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .status-confirmed {
            background: rgba(16, 185, 129, 0.2);
            color: var(--accent-success);
        }

        .status-pending {
            background: rgba(245, 158, 11, 0.2);
            color: var(--accent-warning);
        }

        .status-completed {
            background: rgba(59, 130, 246, 0.2);
            color: var(--accent-primary);
        }

        .status-cancelled {
            background: rgba(239, 68, 68, 0.2);
            color: var(--accent-danger);
        }

        .booking-body {
            padding: 1.5rem;
        }

        .booking-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .booking-info-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .booking-info-icon {
            width: 40px;
            height: 40px;
            border-radius: 0.5rem;
            background: rgba(59, 130, 246, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent-primary);
            font-size: 1rem;
        }

        .booking-info-content {
            flex: 1;
        }

        .booking-info-label {
            color: var(--text-muted);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.25rem;
        }

        .booking-info-value {
            color: var(--text-primary);
            font-weight: 500;
            font-size: 0.9rem;
        }

        .booking-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        /* Buttons */
        .btn-modern {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            font-size: 0.875rem;
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

        .btn-warning-modern {
            background: var(--accent-warning);
            color: white;
        }

        .btn-warning-modern:hover {
            background: #d97706;
            transform: translateY(-2px);
        }

        .btn-danger-modern {
            background: var(--accent-danger);
            color: white;
        }

        .btn-danger-modern:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }

        .btn-success-modern {
            background: var(--accent-success);
            color: white;
        }

        .btn-success-modern:hover {
            background: #059669;
            transform: translateY(-2px);
        }

        /* Modal Styles */
        .modal-dialog {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: calc(100% - 1rem);
            margin: 0.5rem auto;
        }

        .modal-content-modern {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            max-height: 80vh;
            display: flex;
            flex-direction: column;
        }

        .modal-header-modern {
            background: var(--bg-tertiary);
            border-bottom: 1px solid var(--border-color);
            padding: 1.5rem;
            border-radius: 1rem 1rem 0 0;
        }

        .modal-body-modern {
            padding: 1.5rem;
            overflow-y: auto;
        }

        .modal-footer-modern {
            background: var(--bg-tertiary);
            border-top: 1px solid var(--border-color);
            padding: 1.5rem;
            border-radius: 0 0 1rem 1rem;
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

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--text-secondary);
        }

        .empty-state-icon {
            font-size: 4rem;
            color: var(--text-muted);
            margin-bottom: 1rem;
        }

        .empty-state-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }

        .empty-state-description {
            margin-bottom: 2rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
            }
            .stat-card {
                display: grid;
                grid-template-columns: auto 1fr;
                grid-template-rows: auto auto;
                padding: 0.75rem 0.6rem;
                gap: 0 0.5rem;
                text-align: left;
                align-items: center;
                border-radius: 0.75rem;
            }
            .stat-card .stat-icon {
                grid-row: 1 / span 2;
                margin: 0;
                width: 38px; height: 38px;
                font-size: 1.1rem;
                border-radius: 0.5rem;
            }
            .stat-card .stat-number {
                grid-row: 1;
                font-size: 1.25rem;
                margin-bottom: 0;
                align-self: end;
                line-height: 1;
            }
            .stat-card .stat-label {
                grid-row: 2;
                font-size: 0.6rem;
                align-self: start;
                margin-top: 0.2rem;
            }

            .page-header {
                padding: 1.25rem 1rem;
                margin-bottom: 1.5rem;
            }
            .page-title {
                font-size: 1.5rem;
            }
            .page-subtitle {
                font-size: 0.85rem;
            }
            .search-bar-container {
                padding: 0.75rem;
            }
            .premium-search-input {
                padding: 0.8rem 1rem 0.8rem 2.8rem;
                font-size: 0.9rem;
            }
            .search-icon {
                left: 1rem;
                font-size: 1.1rem;
            }
            .booking-card {
                margin-bottom: 1rem;
                border-radius: 0.75rem;
            }
            .booking-header {
                padding: 0.8rem 1rem;
            }
            .booking-id {
                font-size: 0.8rem;
            }
            .booking-status {
                padding: 0.2rem 0.6rem;
                font-size: 0.65rem;
            }
            .booking-body {
                padding: 1rem;
            }
            .booking-info {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 0.6rem;
                margin-bottom: 0.8rem;
            }
            .booking-info-item:nth-child(1) {
                grid-column: 1 / -1;
            }
            .booking-info-item {
                gap: 0.4rem;
            }
            .booking-info-icon {
                width: 28px;
                height: 28px;
                font-size: 0.75rem;
                border-radius: 0.35rem;
            }
            .booking-info-label {
                font-size: 0.6rem;
                margin-bottom: 0.1rem;
            }
            .booking-info-value {
                font-size: 0.8rem;
            }
            .booking-actions {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 0.5rem;
            }
            .btn-modern {
                padding: 0.4rem 0.5rem;
                font-size: 0.75rem;
                justify-content: center;
                width: 100%;
            }
            
            /* Center & Shrink Modal */
            .modal-dialog {
                min-height: calc(100% - 2rem);
                margin: 1rem auto;
                width: 88%;
            }
            .modal-content-modern {
                max-height: 55vh;
            }
            .modal-footer-modern {
                padding: 1rem;
            }
            .modal-body-modern {
                padding: 1rem;
            }
            .modal-header-modern {
                padding: 1rem;
            }
        }

        .unread-highlight {
            border: 1px solid #22c55e !important;
            box-shadow: 0 0 12px rgba(34,197,94,0.4) !important;
        }
    </style>
</head>
<body>
    <!-- Modern Navigation -->
    <?php include 'components/navigation.php'; ?>

    <!-- Main Container -->
    <div class="container-fluid px-4 bookings-container">
        <!-- Success Alert -->
        <div id="successAlert" class="alert alert-modern alert-success-modern alert-dismissible fade show d-none" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <span id="successMessage"></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">My Bookings</h1>
            <p class="page-subtitle">Manage your activity bookings and reservations</p>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div class="stat-number"><?php echo count($bookings); ?></div>
                <div class="stat-label">Total Bookings</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="stat-number"><?php echo $stats['pending_review']; ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-number"><?php echo $stats['approved']; ?></div>
                <div class="stat-label">Approved</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon danger">
                    <i class="bi bi-x-circle"></i>
                </div>
                <div class="stat-number"><?php echo $stats['cancelled']; ?></div>
                <div class="stat-label">Cancelled</div>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="search-bar-container">
            <div class="search-input-wrapper">
                <i class="bi bi-search search-icon"></i>
                <input type="text" id="bookingSearchInput" class="premium-search-input" placeholder="Search by Booking ID, Activity, Date, or Status..." onkeyup="searchBookings()">
            </div>
        </div>

        <!-- Bookings List -->
        <div id="bookingsList">
            <?php if (count($bookings) > 0): ?>
                <?php foreach ($bookings as $booking): ?>
                    <?php 
                        $isUnread = ($booking['status_name'] === 'pending_review' && $booking['created_at'] > $lastVisitedBookings);
                        $highlightClass = $isUnread ? ' unread-highlight' : '';
                    ?>
                    <div class="booking-card<?php echo $highlightClass; ?>" data-status="<?php echo $booking['status_name']; ?>" data-activity="<?php echo $booking['item_name']; ?>">
                        <div class="booking-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="booking-status status-<?php echo $booking['status_name']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $booking['status_name'])); ?>
                                </span>
                                <?php if ($booking['status_name'] === 'awaiting_payment' && !empty($booking['expires_at'])): ?>
                                    <span class="badge bg-warning text-dark d-inline-flex align-items-center gap-1 px-2 py-1" style="font-size: 0.78rem; border-radius: 0.4rem; font-weight: 700;">
                                        <i class="bi bi-clock-history"></i>
                                        Pay within: <span class="countdown-timer" data-remaining="<?php echo max(0, (int)($booking['seconds_remaining'] ?? 0)); ?>">--:--</span>
                                    </span>
                                <?php elseif ($booking['status_name'] === 'payment_verifying'): ?>
                                    <span class="badge d-inline-flex align-items-center gap-1 px-2 py-1" style="background: rgba(139,92,246,0.2); color: #c4b5fd; border: 1px solid rgba(139,92,246,0.4); font-size: 0.78rem; border-radius: 0.4rem; font-weight: 600;">
                                        <i class="bi bi-hourglass-split"></i> Verifying Payment
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="booking-body">
                            <div class="booking-info">
                                <div class="booking-info-item">
                                    <div class="booking-info-icon">
                                        <i class="bi <?php echo $booking['item_icon'] ?: 'bi-calendar-check'; ?>"></i>
                                    </div>
                                    <div class="booking-info-content">
                                        <div class="booking-info-label"><?php echo ucfirst($booking['booking_type']); ?></div>
                                        <div class="booking-info-value"><?php echo htmlspecialchars($booking['item_name']); ?></div>
                                    </div>
                                </div>
                                <div class="booking-info-item">
                                    <div class="booking-info-icon">
                                        <i class="bi bi-calendar"></i>
                                    </div>
                                    <div class="booking-info-content">
                                        <div class="booking-info-label">Date</div>
                                        <div class="booking-info-value"><?php echo date('F j, Y', strtotime($booking['check_in_date'])); ?></div>
                                    </div>
                                </div>
                                <?php if ($booking['check_in_time']): ?>
                                <div class="booking-info-item">
                                    <div class="booking-info-icon">
                                        <i class="bi bi-clock"></i>
                                    </div>
                                    <div class="booking-info-content">
                                        <div class="booking-info-label">Time</div>
                                        <div class="booking-info-value">
                                            <?php echo date('g:i A', strtotime($booking['check_in_time'])); ?>
                                            <?php if ($booking['check_out_time']): ?>
                                                - <?php echo date('g:i A', strtotime($booking['check_out_time'])); ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <div class="booking-info-item">
                                    <div class="booking-info-icon">
                                        <span style="font-weight: 800; font-style: normal; font-size: 1.2rem; line-height: 1;">&#8369;</span>
                                    </div>
                                    <div class="booking-info-content">
                                        <div class="booking-info-label">Total Amount</div>
                                        <div class="booking-info-value">&#8369;<?php echo number_format($booking['total_amount'], 2); ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="booking-actions">
                                <button class="btn-modern btn-primary-modern" onclick="viewBooking(<?php echo $booking['id']; ?>)">
                                    <i class="bi bi-eye"></i>View Details
                                </button>
                                <?php if ($booking['status_name'] === 'awaiting_payment'): ?>
                                <button class="btn-modern btn-warning-modern pay-now-btn" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white; font-weight: 700; box-shadow: 0 4px 12px rgba(245,158,11,0.25);" onclick='openPaymentModal(<?php echo htmlspecialchars(json_encode([
                                    "id" => (int)$booking["id"],
                                    "booking_reference" => $booking["booking_reference"],
                                    "item_name" => $booking["item_name"],
                                    "total_amount" => (float)$booking["total_amount"],
                                    "expires_at" => $booking["expires_at"],
                                    "remaining_seconds" => max(0, (int)($booking["seconds_remaining"] ?? 0))
                                ]), ENT_QUOTES, "UTF-8"); ?>)'>
                                    <i class="bi bi-qr-code-scan"></i> Pay Now (GCash)
                                </button>
                                <?php elseif ($booking['status_name'] === 'approved'): ?>
                                <button class="btn-modern btn-danger-modern" onclick="cancelBooking(<?php echo $booking['id']; ?>)">
                                    <i class="bi bi-x-circle"></i>Cancel Booking
                                </button>
                                <?php elseif ($booking['status_name'] === 'completed'): ?>
                                <button class="btn-modern btn-primary-modern" onclick="rebookActivity(<?php echo $booking['id']; ?>)">
                                    <i class="bi bi-arrow-repeat"></i>Book Again
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div id="emptyState" class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bi bi-calendar-x"></i>
                    </div>
                    <h3 class="empty-state-title">No bookings found</h3>
                    <p class="empty-state-description">
                        You haven't made any bookings yet. Start by booking an activity or accommodation.
                    </p>
                    <a href="create-booking.php" class="btn-modern btn-primary-modern">
                        <i class="bi bi-calendar-plus"></i>Create Your First Booking
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Empty State (hidden by default) -->
        <div id="emptyState" class="empty-state" style="display: none;">
            <div class="empty-state-icon">
                <i class="bi bi-calendar-x"></i>
            </div>
            <h3 class="empty-state-title">No bookings found</h3>
            <p class="empty-state-description">
                You don't have any bookings matching your current filters.
            </p>
            <button class="btn-modern btn-primary-modern" onclick="resetFilters()">
                <i class="bi bi-arrow-clockwise"></i>Reset Filters
            </button>
            <button class="btn-modern btn-secondary-modern" onclick="window.location.href='../index.html#booking'">
                <i class="bi bi-plus-circle"></i>Make a Booking
            </button>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         PAYMENT MODAL (GCASH 2-HOUR WINDOW & CANVAS COMPRESSION)
    ════════════════════════════════════════════════════════════ -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
            <div class="modal-content modal-content-modern" style="background: linear-gradient(145deg, #1e293b, #0f172a); border: 1px solid rgba(245,158,11,0.3); border-radius: 1.25rem; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5), 0 0 30px rgba(245,158,11,0.15);">
                <!-- Header -->
                <div class="modal-header modal-header-modern" style="background: rgba(15,23,42,0.6); border-bottom: 1px solid rgba(255,255,255,0.08); padding: 1.25rem 1.5rem; border-radius: 1.25rem 1.25rem 0 0;">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width: 36px; height: 36px; border-radius: 0.5rem; background: linear-gradient(135deg, #0284c7, #0369a1); display: flex; align-items: center; justify-content: center; color: white;">
                            <i class="bi bi-wallet2" style="font-size: 1.1rem;"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0" id="paymentModalLabel" style="font-weight: 700; font-size: 1.05rem; color: #f8fafc;">GCash Payment</h5>
                            <span style="font-size: 0.72rem; color: #94a3b8;">Complete payment to confirm reservation</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Body -->
                <div class="modal-body modal-body-modern" style="padding: 1.5rem; overflow-y: auto;">
                    <!-- Countdown Banner -->
                    <div id="modalCountdownBox" style="background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.3); border-radius: 0.75rem; padding: 0.75rem 1rem; margin-bottom: 1.25rem; display: flex; align-items: center; justify-content: space-between;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-clock-history" style="color: #f59e0b; font-size: 1.2rem;"></i>
                            <div>
                                <div style="font-size: 0.72rem; color: #cbd5e1; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">Time Remaining</div>
                                <div id="modalCountdownText" style="font-size: 1.15rem; font-weight: 800; color: #fbbf24; font-variant-numeric: tabular-nums;">--:--:--</div>
                            </div>
                        </div>
                        <span class="badge" style="background: rgba(245,158,11,0.2); color: #fbbf24; font-size: 0.7rem; font-weight: 600;">2-Hour Limit</span>
                    </div>

                    <!-- Price Summary Card -->
                    <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 0.75rem; padding: 1rem; margin-bottom: 1.25rem;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span style="font-size: 0.8rem; color: #94a3b8;">Reserved Item:</span>
                            <span id="modalPayItem" style="font-size: 0.85rem; font-weight: 600; color: #f1f5f9;">—</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span style="font-size: 0.8rem; color: #94a3b8;">Reference:</span>
                            <span id="modalPayRef" style="font-size: 0.82rem; font-family: monospace; color: #38bdf8; font-weight: 700;">—</span>
                        </div>
                        <div style="border-top: 1px dashed rgba(255,255,255,0.1); margin-top: 0.5rem; padding-top: 0.5rem;" class="d-flex justify-content-between align-items-center">
                            <span style="font-size: 0.9rem; font-weight: 600; color: #f8fafc;">Total Amount / Fee:</span>
                            <span id="modalPayAmount" style="font-size: 1.35rem; font-weight: 800; color: #10b981;">₱0.00</span>
                        </div>
                    </div>

                    <!-- QR Code Section -->
                    <div style="text-align: center; margin-bottom: 1.25rem;">
                        <div style="font-size: 0.75rem; color: #94a3b8; font-weight: 600; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em;">
                            <i class="bi bi-qr-code me-1"></i>Scan GCash QR Code
                        </div>
                        <div style="display: inline-block; background: #ffffff; padding: 0.65rem; border-radius: 0.85rem; box-shadow: 0 10px 25px rgba(0,0,0,0.3); max-width: 220px; width: 100%;">
                            <a href="../assets/gcash/gcashqr.jpg" target="_blank" title="Click to view full size">
                                <img src="../assets/gcash/gcashqr.jpg" alt="GCash QR" style="width: 100%; height: auto; aspect-ratio: 1/1; object-fit: contain; display: block; border-radius: 0.4rem;" onerror="this.src='../assets/gcash/gcash_qr.png';">
                            </a>
                        </div>
                        <div style="font-size: 0.72rem; color: #94a3b8; margin-top: 0.45rem;">
                            <i class="bi bi-zoom-in me-1"></i>Click / tap image to view full size
                        </div>
                    </div>

                    <!-- Receipt Upload & Compression Form -->
                    <div style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); border-radius: 0.75rem; padding: 1rem;">
                        <label class="form-label" style="font-size: 0.8rem; font-weight: 600; color: #e2e8f0; margin-bottom: 0.4rem; display: flex; align-items: center; justify-content: space-between;">
                            <span><i class="bi bi-image me-1"></i>Upload Payment Screenshot</span>
                            <span style="font-size: 0.7rem; color: #94a3b8;">Max 2MB (Auto-compressed)</span>
                        </label>
                        <input type="file" id="payModalFileInput" class="form-control" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.15); color: #f8fafc; font-size: 0.82rem; border-radius: 0.5rem; padding: 0.45rem 0.65rem;" onchange="if(this.files && this.files[0]) handleReceiptFileSelect(this.files[0]);">
                        <div style="font-size: 0.7rem; color: #94a3b8; margin-top: 0.3rem;">
                            Accepted formats: JPG, JPEG, WEBP, PNG.
                        </div>

                        <!-- Compression preview box -->
                        <div id="compressPreviewBox" style="display: none; margin-top: 0.75rem; padding: 0.6rem; background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.3); border-radius: 0.5rem;">
                            <div class="d-flex align-items-center gap-2">
                                <img id="compressThumb" src="" alt="Thumbnail" style="width: 48px; height: 48px; object-fit: cover; border-radius: 0.35rem; border: 1px solid rgba(255,255,255,0.2);">
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-size: 0.75rem; font-weight: 700; color: #34d399;"><i class="bi bi-check-circle-fill me-1"></i>Compressed Ready</div>
                                    <div id="compressStatsText" style="font-size: 0.7rem; color: #cbd5e1;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer modal-footer-modern" style="background: rgba(15,23,42,0.6); border-top: 1px solid rgba(255,255,255,0.08); padding: 1rem 1.5rem; border-radius: 0 0 1.25rem 1.25rem; display: flex; gap: 0.75rem;">
                    <button type="button" class="btn-modern btn-secondary-modern" data-bs-dismiss="modal" style="flex: 1; justify-content: center;">Cancel</button>
                    <button type="button" class="btn-modern btn-primary-modern" id="payModalSubmitBtn" disabled style="flex: 2; justify-content: center; background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 4px 15px rgba(16,185,129,0.3);" onclick="submitPaymentReceipt()">
                        <i class="bi bi-upload"></i> Submit Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Details Modal -->
    <div class="modal fade" id="bookingModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modal-content-modern">
                <div class="modal-header modal-header-modern">
                    <h5 class="modal-title">
                        <i class="bi bi-info-circle me-2"></i>Booking Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body modal-body-modern">
                    <div id="bookingDetails">
                        <!-- Booking details will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer modal-footer-modern d-flex justify-content-end gap-2" id="bookingModalFooter">
                    <button type="button" class="btn-modern btn-secondary-modern" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="viewBundleBtn" class="btn-modern align-items-center justify-content-center gap-2" style="display: none; background: linear-gradient(135deg, rgba(157,255,0,0.15), rgba(0,212,255,0.15)); border: 1px solid rgba(157,255,0,0.5); color: #9dff00; border-radius: 8px; padding: 0.5rem 1rem; font-weight: 600; font-size: 0.8rem; transition: all 0.2s ease;" onmouseover="this.style.background='linear-gradient(135deg, rgba(157,255,0,0.3), rgba(0,212,255,0.25))';this.style.boxShadow='0 0 15px rgba(157,255,0,0.3)'" onmouseout="this.style.background='linear-gradient(135deg, rgba(157,255,0,0.15), rgba(0,212,255,0.15))';this.style.boxShadow='none'" onclick="openBundleModal()">
                        <i class="bi bi-box-seam"></i> Bundle Details
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bundle Breakdown Modal -->
    <div class="modal fade" id="bundleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modal-content-modern">
                <div class="modal-header modal-header-modern">
                    <h5 class="modal-title">
                        <i class="bi bi-box-seam me-2" style="color: #9dff00;"></i>Bundle & Booking Breakdown
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body modal-body-modern">
                    <div id="bundleDetails">
                        <!-- Bundle details loaded here -->
                    </div>
                </div>
                <div class="modal-footer modal-footer-modern">
                    <button type="button" class="btn-modern btn-secondary-modern" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Unified Confirmation Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-modern" style="border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; background: #0f172a;">
                <div class="modal-header modal-header-modern" style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <h5 class="modal-title" id="confirmModalTitle">
                        <i class="bi bi-question-circle me-2" style="color: #00d4ff;"></i>Confirmation
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body modal-body-modern text-center p-4">
                    <p id="confirmModalMessage" style="font-size: 1.1rem; color: #f8fafc; margin-bottom: 0;">Are you sure?</p>
                </div>
                <div class="modal-footer modal-footer-modern d-flex justify-content-center" style="border-top: none; padding-bottom: 1.5rem;">
                    <button type="button" class="btn-modern btn-secondary-modern px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn-modern px-4" id="confirmModalActionBtn" style="background: linear-gradient(135deg, #00d4ff 0%, #0077ff 100%); color: white; border: none;">Confirm</button>
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
            
            // Remove previous event listeners by cloning
            const newActionBtn = actionBtn.cloneNode(true);
            actionBtn.parentNode.replaceChild(newActionBtn, actionBtn);
            
            newActionBtn.addEventListener('click', () => {
                bootstrap.Modal.getInstance(document.getElementById('confirmModal')).hide();
                callback();
            });
            
            new bootstrap.Modal(document.getElementById('confirmModal')).show();
        }

        // Booking Data & Functions
        const allUserBookings = <?php echo json_encode(array_column($bookings, null, 'id'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
        let currentBundleId = null;

        function escHtml(str) {
            if (str == null) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function formatBookingDate(dateStr) {
            if (!dateStr) return '—';
            try {
                const parts = dateStr.split('-');
                if (parts.length === 3) {
                    const d = new Date(parts[0], parts[1] - 1, parts[2]);
                    return d.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
                }
                return dateStr;
            } catch(e) {
                return dateStr;
            }
        }

        function formatBookingTime(timeStr) {
            if (!timeStr) return '';
            try {
                const parts = timeStr.split(':');
                const h = parseInt(parts[0], 10);
                const m = parseInt(parts[1], 10);
                const ampm = h >= 12 ? 'PM' : 'AM';
                const h12 = h % 12 || 12;
                return `${h12}:${String(m).padStart(2, '0')} ${ampm}`;
            } catch(e) {
                return timeStr;
            }
        }

        function viewBooking(bookingId) {
            const booking = allUserBookings[bookingId];
            if (!booking) {
                console.warn("Booking not found for ID:", bookingId);
                return;
            }

            currentBundleId = booking.id;

            const checkInFmt = formatBookingDate(booking.check_in_date);
            let dateDisplay = checkInFmt;
            if (booking.check_out_date && booking.check_out_date !== booking.check_in_date) {
                dateDisplay = `${checkInFmt} – ${formatBookingDate(booking.check_out_date)}`;
            }

            let timeDisplay = 'Standard Resort Hours';
            if (booking.check_in_time) {
                timeDisplay = formatBookingTime(booking.check_in_time);
                if (booking.check_out_time) {
                    timeDisplay += ' – ' + formatBookingTime(booking.check_out_time);
                }
            }

            let durationDisplay = '1 Day';
            if (booking.number_of_days && parseInt(booking.number_of_days, 10) > 1) {
                durationDisplay = `${booking.number_of_days} Night(s)`;
            } else if (booking.booking_type === 'activity') {
                durationDisplay = `${booking.number_of_guests || 1} Pax`;
            }

            const statusColor = booking.status_color || '#3b82f6';
            const statusLabel = (booking.status_name || 'Pending').replace(/_/g, ' ').toUpperCase();
            const payStatusLabel = (booking.payment_status || 'Pending').toUpperCase();

            // Toggle Bundle Details button
            const bundleBtn = document.getElementById('viewBundleBtn');
            if (bundleBtn) {
                if (booking.booking_type === 'bundle') {
                    bundleBtn.style.display = 'inline-flex';
                } else {
                    bundleBtn.style.display = 'none';
                }
            }

            const specialReqsHtml = (booking.special_requests && booking.special_requests.trim())
                ? escHtml(booking.special_requests).replace(/\n/g, '<br>')
                : '<em>No special requests or add-ons specified.</em>';

            document.getElementById('bookingDetails').innerHTML = `
                <div class="d-flex align-items-center justify-content-between p-3 mb-4 rounded" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                    <div>
                        <div class="text-muted small">Booking Reference</div>
                        <div class="fw-bold text-primary fs-5" style="letter-spacing: 0.5px;">${escHtml(booking.booking_reference)}</div>
                    </div>
                    <div class="text-end">
                        <span class="badge px-3 py-2 me-1" style="background: ${statusColor}; color: #fff; font-size: 0.8rem; font-weight: 700;">
                            ${escHtml(statusLabel)}
                        </span>
                        <span class="badge px-2 py-2 ${booking.payment_status === 'paid' ? 'bg-success' : 'bg-warning text-dark'}" style="font-size: 0.8rem; font-weight: 700;">
                            ${escHtml(payStatusLabel)}
                        </span>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3"><i class="bi bi-info-circle me-1"></i>Booking Information</h6>
                        <div class="booking-info">
                            <div class="booking-info-item">
                                <div class="booking-info-icon">
                                    <i class="bi ${escHtml(booking.item_icon || 'bi-house-door')}"></i>
                                </div>
                                <div class="booking-info-content">
                                    <div class="booking-info-label">${escHtml((booking.booking_type || 'Package').toUpperCase())}</div>
                                    <div class="booking-info-value fw-bold text-white">${escHtml(booking.item_name || 'Reservation')}</div>
                                </div>
                            </div>
                            <div class="booking-info-item">
                                <div class="booking-info-icon">
                                    <i class="bi bi-calendar-event"></i>
                                </div>
                                <div class="booking-info-content">
                                    <div class="booking-info-label">Date</div>
                                    <div class="booking-info-value">${escHtml(dateDisplay)}</div>
                                </div>
                            </div>
                            <div class="booking-info-item">
                                <div class="booking-info-icon">
                                    <i class="bi bi-clock"></i>
                                </div>
                                <div class="booking-info-content">
                                    <div class="booking-info-label">Schedule / Time</div>
                                    <div class="booking-info-value">${escHtml(timeDisplay)}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3"><i class="bi bi-card-checklist me-1"></i>Reservation Details</h6>
                        <div class="booking-info">
                            <div class="booking-info-item">
                                <div class="booking-info-icon">
                                    <i class="bi bi-people"></i>
                                </div>
                                <div class="booking-info-content">
                                    <div class="booking-info-label">Number of Guests</div>
                                    <div class="booking-info-value">${parseInt(booking.number_of_guests, 10) || 1} Guest(s)</div>
                                </div>
                            </div>
                            <div class="booking-info-item">
                                <div class="booking-info-icon">
                                    <i class="bi bi-hourglass-split"></i>
                                </div>
                                <div class="booking-info-content">
                                    <div class="booking-info-label">Duration / Stay</div>
                                    <div class="booking-info-value">${escHtml(durationDisplay)}</div>
                                </div>
                            </div>
                            <div class="booking-info-item">
                                <div class="booking-info-icon">
                                    <span style="font-weight: 800; font-style: normal; font-size: 1.1rem; line-height: 1;">₱</span>
                                </div>
                                <div class="booking-info-content">
                                    <div class="booking-info-label">Total Amount</div>
                                    <div class="booking-info-value fw-bold text-success fs-5">₱${parseFloat(booking.total_amount || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <h6 class="text-primary mb-2"><i class="bi bi-chat-left-dots me-1"></i>Special Requests & Add-ons</h6>
                        <div class="p-3 rounded" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); font-size: 0.9rem; line-height: 1.5; color: var(--text-secondary, #94a3b8);">
                            ${specialReqsHtml}
                        </div>
                    </div>
                </div>
            `;

            new bootstrap.Modal(document.getElementById('bookingModal')).show();
        }

        function openBundleModal() {
            if (!currentBundleId) return;
            const booking = allUserBookings[currentBundleId];
            if (!booking) return;

            const bundleTitle = booking.item_name || 'Bundle Package';
            const bundleDesc = booking.bundle_description || 'Exclusive bundle package with full resort amenities.';
            const totalFmt = '₱' + parseFloat(booking.total_amount || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            document.getElementById('bundleDetails').innerHTML = `
                <div class="mb-4 p-3 rounded" style="background: rgba(157,255,0,0.05); border: 1px solid rgba(157,255,0,0.2);">
                    <div class="fw-bold text-white mb-1" style="font-size: 1.05rem;">
                        <i class="bi bi-stars me-2" style="color: #9dff00;"></i>${escHtml(bundleTitle)}
                    </div>
                    <div style="color: #94a3b8; font-size: 0.875rem;">${escHtml(bundleDesc)}</div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4 pt-3" style="border-top: 1px solid rgba(255,255,255,0.1);">
                    <span class="fw-bold text-white" style="font-size: 1rem;">Total Amount</span>
                    <span class="fw-bold" style="font-size: 1.4rem; color: #9dff00;">${totalFmt}</span>
                </div>
            `;

            const bookingModalEl = document.getElementById('bookingModal');
            const bundleModalEl = document.getElementById('bundleModal');
            bootstrap.Modal.getInstance(bookingModalEl)?.hide();
            bookingModalEl.addEventListener('hidden.bs.modal', function handler() {
                new bootstrap.Modal(bundleModalEl).show();
                bookingModalEl.removeEventListener('hidden.bs.modal', handler);
            });
        }

        function rescheduleBooking(bookingId) {
            showConfirmModal('Reschedule Booking', `Do you want to reschedule booking ${bookingId}?`, 'Reschedule', '#f59e0b', () => {
                showSuccess(`Reschedule functionality would open a calendar for booking: ${bookingId}`);
            });
        }

        function cancelBooking(bookingId) {
            showConfirmModal('Cancel Booking', `Are you sure you want to cancel booking ${bookingId}?`, 'Yes, Cancel', '#ef4444', () => {
                // Remove the booking card
                const bookingCard = document.querySelector(`[data-booking-id="${bookingId}"]`) || 
                                 document.querySelector(`.booking-card:has(.booking-id:contains("${bookingId}"))`);
                if (bookingCard) {
                    bookingCard.style.opacity = '0.5';
                    setTimeout(() => {
                        bookingCard.remove();
                        showSuccess(`Booking ${bookingId} cancelled and refunded`);
                        updateStats();
                    }, 500);
                } else {
                    showSuccess(`Booking ${bookingId} would be cancelled and refunded`);
                }
            });
        }

        function rateActivity(bookingId) {
            const rating = prompt('Rate your experience (1-5 stars):');
            if (rating && rating >= 1 && rating <= 5) {
                showSuccess(`Thank you for rating booking ${bookingId} with ${rating} stars!`);
            } else if (rating) {
                showError('Please enter a rating between 1 and 5');
            }
        }

        function rebookActivity(bookingId) {
            showConfirmModal('Rebook Activity', 'Do you want to rebook this activity?', 'Rebook', '#10b981', () => {
                window.location.href = '../index.html#booking';
            });
        }

        // Search Functionality
        function searchBookings() {
            const query = document.getElementById('bookingSearchInput').value.toLowerCase();
            const bookingCards = document.querySelectorAll('.booking-card');
            let visibleCount = 0;
            
            bookingCards.forEach(card => {
                const textContent = card.textContent.toLowerCase();
                
                if (textContent.includes(query)) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Show/hide empty state
            const emptyState = document.getElementById('emptyState');
            const bookingsList = document.getElementById('bookingsList');
            
            if (visibleCount === 0) {
                emptyState.style.display = 'block';
                bookingsList.style.display = 'none';
            } else {
                emptyState.style.display = 'none';
                bookingsList.style.display = 'block';
            }
        }

        function printBooking() {
            window.print();
        }

        function updateStats() {
            // Update stats after booking cancellation
            const confirmedCount = document.querySelectorAll('.booking-card[data-status="confirmed"]:not([style*="display: none"])').length;
            const pendingCount = document.querySelectorAll('.booking-card[data-status="pending"]:not([style*="display: none"])').length;
            const completedCount = document.querySelectorAll('.booking-card[data-status="completed"]:not([style*="display: none"])').length;
            
            document.querySelector('.stat-card:nth-child(1) .stat-number').textContent = confirmedCount;
            document.querySelector('.stat-card:nth-child(2) .stat-number').textContent = pendingCount;
            document.querySelector('.stat-card:nth-child(3) .stat-number').textContent = completedCount;
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

        // ═══════════════════════════════════════════════════════════════
        // GCASH PAYMENT MODAL, CLIENT-SIDE COMPRESSION & LIVE TIMER
        // ═══════════════════════════════════════════════════════════════
        let currentPayBooking = null;
        let compressedReceiptBlob = null;
        let paymentModalObj = null;

        function openPaymentModal(booking) {
            currentPayBooking = booking;
            compressedReceiptBlob = null;
            const remainingSec = parseInt(booking.remaining_seconds != null ? booking.remaining_seconds : 0, 10);
            currentPayBooking.targetTime = Date.now() + (remainingSec * 1000);

            document.getElementById('modalPayItem').textContent = booking.item_name || 'Reservation';
            document.getElementById('modalPayRef').textContent = booking.booking_reference || '';
            document.getElementById('modalPayAmount').textContent = '₱1,000.00';
            
            // Reset file input & preview
            const fileInp = document.getElementById('payModalFileInput');
            if (fileInp) fileInp.value = '';
            const previewBox = document.getElementById('compressPreviewBox');
            if (previewBox) previewBox.style.display = 'none';
            const submitBtn = document.getElementById('payModalSubmitBtn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-upload"></i> Submit Receipt';
            }

            if (!paymentModalObj) {
                paymentModalObj = new bootstrap.Modal(document.getElementById('paymentModal'));
            }
            updateModalTimer();
            paymentModalObj.show();
        }

        // ── Canvas API Image Compressor ─────────────────────────────────
        function compressReceiptImage(file) {
            return new Promise((resolve, reject) => {
                const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    return reject(new Error('Invalid file type. Please select a JPG, PNG, or WEBP image.'));
                }
                if (file.size > 2 * 1024 * 1024) {
                    return reject(new Error('File size exceeds 2MB limit. Please choose a smaller image.'));
                }

                const reader = new FileReader();
                reader.onerror = () => reject(new Error('Failed to read image file.'));
                reader.onload = (e) => {
                    const img = new Image();
                    img.onerror = () => reject(new Error('Invalid image file.'));
                    img.onload = () => {
                        const maxDim = 1200;
                        let width = img.naturalWidth || img.width;
                        let height = img.naturalHeight || img.height;

                        if (width > maxDim || height > maxDim) {
                            if (width > height) {
                                height = Math.round((height * maxDim) / width);
                                width = maxDim;
                            } else {
                                width = Math.round((width * maxDim) / height);
                                height = maxDim;
                            }
                        }

                        const canvas = document.createElement('canvas');
                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);

                        canvas.toBlob(blob => {
                            if (!blob) {
                                return reject(new Error('Canvas compression failed.'));
                            }
                            resolve({
                                blob: blob,
                                originalSize: file.size,
                                compressedSize: blob.size,
                                previewUrl: URL.createObjectURL(blob)
                            });
                        }, 'image/jpeg', 0.82);
                    };
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
            });
        }

        async function handleReceiptFileSelect(file) {
            const submitBtn = document.getElementById('payModalSubmitBtn');
            const previewBox = document.getElementById('compressPreviewBox');
            const thumb = document.getElementById('compressThumb');
            const stats = document.getElementById('compressStatsText');

            try {
                const compressed = await compressReceiptImage(file);
                compressedReceiptBlob = compressed.blob;
                thumb.src = compressed.previewUrl;
                
                const origKB = (compressed.originalSize / 1024).toFixed(0);
                const compKB = (compressed.compressedSize / 1024).toFixed(0);
                const savedPercent = Math.max(0, Math.round((1 - compressed.compressedSize / compressed.originalSize) * 100));
                stats.innerHTML = `Compressed: <strong>${compKB} KB</strong> (from ${origKB} KB, -${savedPercent}%)`;
                
                previewBox.style.display = 'block';
                submitBtn.disabled = false;
            } catch(err) {
                compressedReceiptBlob = null;
                if (previewBox) previewBox.style.display = 'none';
                if (submitBtn) submitBtn.disabled = true;
                showError(err.message || 'Error processing image.');
            }
        }

        async function submitPaymentReceipt() {
            if (!currentPayBooking || !compressedReceiptBlob) {
                showError('Please attach your GCash payment screenshot.');
                return;
            }

            const submitBtn = document.getElementById('payModalSubmitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading...';

            const fd = new FormData();
            fd.append('booking_id', currentPayBooking.id);
            fd.append('receipt', compressedReceiptBlob, 'receipt_' + currentPayBooking.id + '.jpg');

            try {
                const res = await fetch('../php/submit_payment_receipt.php', {
                    method: 'POST',
                    body: fd
                });
                const result = await res.json();
                if (result.success) {
                    if (paymentModalObj) paymentModalObj.hide();
                    showSuccess(result.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showError(result.message || 'Failed to submit payment receipt.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-upload"></i> Submit Receipt';
                }
            } catch(e) {
                showError('Network error uploading payment receipt.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-upload"></i> Submit Receipt';
            }
        }

        // ── Live 2-Hour Countdown Timer ─────────────────────────────────
        function updateAllCountdowns() {
            // Update badge countdowns on cards
            document.querySelectorAll('.countdown-timer').forEach(el => {
                if (!el.dataset.targetTime) {
                    const remainingSec = parseInt(el.getAttribute('data-remaining') || '0', 10);
                    el.dataset.targetTime = Date.now() + (remainingSec * 1000);
                }
                const target = parseInt(el.dataset.targetTime, 10);
                const diff = Math.floor((target - Date.now()) / 1000);
                if (diff <= 0) {
                    el.textContent = 'Expired';
                    el.closest('.badge')?.classList.replace('bg-warning', 'bg-danger');
                } else {
                    const h = Math.floor(diff / 3600);
                    const m = Math.floor((diff % 3600) / 60);
                    const s = diff % 60;
                    el.textContent = `${h}h ${String(m).padStart(2,'0')}m ${String(s).padStart(2,'0')}s`;
                }
            });

            updateModalTimer();
        }

        function updateModalTimer() {
            if (!currentPayBooking || currentPayBooking.targetTime === undefined) return;
            const diff = Math.floor((currentPayBooking.targetTime - Date.now()) / 1000);
            const textEl = document.getElementById('modalCountdownText');
            const submitBtn = document.getElementById('payModalSubmitBtn');

            if (!textEl) return;

            if (diff <= 0) {
                textEl.textContent = '00:00:00 (Expired)';
                textEl.style.color = '#ef4444';
                if (submitBtn) submitBtn.disabled = true;
            } else {
                const h = Math.floor(diff / 3600);
                const m = Math.floor((diff % 3600) / 60);
                const s = diff % 60;
                textEl.textContent = `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
                textEl.style.color = diff < 900 ? '#ef4444' : '#fbbf24';
                if (submitBtn && compressedReceiptBlob) submitBtn.disabled = false;
            }
        }

        setInterval(updateAllCountdowns, 1000);
        document.addEventListener('DOMContentLoaded', updateAllCountdowns);
    </script>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
