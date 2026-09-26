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

/** @var mysqli $conn */
global $conn;

// Get user info
$userName = $_SESSION['user_name'];
$userEmail = $_SESSION['user_email'];
$userId = $_SESSION['user_id'];

// Get user bookings from database
$stmt = $conn->prepare("
    SELECT 
        b.id,
        b.booking_reference,
        b.booking_type,
        b.activity_id,
        b.accommodation_id,
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
        b.created_at,
        bs.name as status_name,
        bs.description as status_description,
        bs.color as status_color,
        CASE 
            WHEN b.booking_type = 'activity' THEN a.name
            ELSE acc.name
        END as item_name,
        CASE 
            WHEN b.booking_type = 'activity' THEN 'bi-activity'
            ELSE NULL
        END as item_icon
    FROM bookings b
    LEFT JOIN booking_status bs ON b.status_id = bs.id
    LEFT JOIN activities a ON b.activity_id = a.id
    LEFT JOIN accommodations acc ON b.accommodation_id = acc.id
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

        /* Filter Bar */
        .filter-bar {
            background: var(--bg-card);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .filter-group {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-select {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .filter-select:focus {
            background: var(--bg-tertiary);
            border-color: var(--accent-primary);
            color: var(--text-primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
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
        .modal-content-modern {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
        }

        .modal-header-modern {
            background: var(--bg-tertiary);
            border-bottom: 1px solid var(--border-color);
            padding: 1.5rem;
            border-radius: 1rem 1rem 0 0;
        }

        .modal-body-modern {
            padding: 1.5rem;
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

            .filter-group {
                flex-direction: column;
                align-items: stretch;
            }

            .booking-info {
                grid-template-columns: 1fr;
            }

            .booking-actions {
                flex-direction: column;
            }

            .btn-modern {
                justify-content: center;
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

        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="filter-group">
                <select class="filter-select" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <select class="filter-select" id="activityFilter">
                    <option value="">All Activities</option>
                    <option value="basketball">Basketball Court</option>
                    <option value="swimming">Swimming Pool</option>
                    <option value="gym">Fitness Gym</option>
                    <option value="adventure">Adventure Rides</option>
                </select>
                <select class="filter-select" id="dateFilter">
                    <option value="">All Time</option>
                    <option value="today">Today</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                </select>
                <button class="btn-modern btn-primary-modern" onclick="applyFilters()">
                    <i class="bi bi-funnel"></i>Apply Filters
                </button>
                <button class="btn-modern btn-secondary-modern" onclick="resetFilters()">
                    <i class="bi bi-arrow-clockwise"></i>Reset
                </button>
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
                        <div class="booking-header">
                            <span class="booking-status status-<?php echo $booking['status_name']; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $booking['status_name'])); ?>
                            </span>
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
                                <?php if ($booking['status_name'] === 'pending_review'): ?>
                                <button class="btn-modern btn-danger-modern" onclick="cancelBooking(<?php echo $booking['id']; ?>)">
                                    <i class="bi bi-x-circle"></i>Cancel Booking
                                </button>
                                <?php elseif ($booking['status_name'] === 'approved'): ?>
                                <button class="btn-modern btn-success-modern" onclick="downloadTicket(<?php echo $booking['id']; ?>)">
                                    <i class="bi bi-download"></i>Download Ticket
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
                <div class="modal-footer modal-footer-modern d-flex justify-content-between align-items-center">
                    <button type="button" class="btn-modern btn-secondary-modern" data-bs-dismiss="modal">Close</button>
                    <div class="d-flex gap-2">
                        <button type="button" id="viewBundleBtn" class="btn d-flex align-items-center gap-2" style="background: linear-gradient(135deg, rgba(157,255,0,0.15), rgba(0,212,255,0.15)); border: 1px solid rgba(157,255,0,0.5); color: #9dff00; border-radius: 8px; padding: 0.5rem 1.2rem; font-weight: 600; font-size: 0.9rem; transition: all 0.2s ease;" onmouseover="this.style.background='linear-gradient(135deg, rgba(157,255,0,0.3), rgba(0,212,255,0.25))';this.style.boxShadow='0 0 15px rgba(157,255,0,0.3)'" onmouseout="this.style.background='linear-gradient(135deg, rgba(157,255,0,0.15), rgba(0,212,255,0.15))';this.style.boxShadow='none'" onclick="openBundleModal()">
                            <i class="bi bi-box-seam"></i> View Bundle Details
                        </button>

                    </div>
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

        // Booking Functions
        function viewBooking(bookingId) {
            const bookingData = {
                'BK001': {
                    activity: 'Basketball Court',
                    date: 'March 25, 2026',
                    time: '10:00 AM - 12:00 PM',
                    duration: '2 hours',
                    status: 'Confirmed',
                    price: '₱25.00',
                    location: 'Court A - Indoor Sports Complex',
                    instructor: 'John Smith',
                    equipment: 'Basketball provided',
                    notes: 'Please arrive 10 minutes early'
                },
                'BK002': {
                    activity: 'Swimming Pool',
                    date: 'March 26, 2026',
                    time: '2:00 PM - 3:00 PM',
                    duration: '1 hour',
                    status: 'Pending',
                    price: '₱15.00',
                    location: 'Main Pool - Recreation Center',
                    instructor: 'Self-guided',
                    equipment: 'Bring your own towel',
                    notes: 'Shower facilities available'
                },
                'BK003': {
                    activity: 'Fitness Gym',
                    date: 'March 24, 2026',
                    time: '6:00 AM - 7:30 AM',
                    duration: '1.5 hours',
                    status: 'Confirmed',
                    price: '₱20.00',
                    location: 'Gym Floor - Fitness Center',
                    instructor: 'Sarah Johnson',
                    equipment: 'All equipment provided',
                    notes: 'Water bottle recommended'
                },
                'BK004': {
                    activity: 'Adventure Rides',
                    date: 'March 23, 2026',
                    time: '3:00 PM - 6:00 PM',
                    duration: '3 hours',
                    status: 'Completed',
                    price: '₱45.00',
                    location: 'Adventure Zone - Outdoor Area',
                    instructor: 'Mike Wilson',
                    equipment: 'Safety gear provided',
                    notes: 'Weather dependent activity'
                }
            };
            
            const booking = bookingData[bookingId] || bookingData['BK001'];
            currentBundleId = bookingId;

            document.getElementById('bookingDetails').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">Booking Information</h6>
                        <div class="booking-info">
                            <div class="booking-info-item">
                                <div class="booking-info-icon">
                                    <i class="bi bi-activity"></i>
                                </div>
                                <div class="booking-info-content">
                                    <div class="booking-info-label">Activity</div>
                                    <div class="booking-info-value">${booking.activity}</div>
                                </div>
                            </div>
                            <div class="booking-info-item">
                                <div class="booking-info-icon">
                                    <i class="bi bi-calendar"></i>
                                </div>
                                <div class="booking-info-content">
                                    <div class="booking-info-label">Date</div>
                                    <div class="booking-info-value">${booking.date}</div>
                                </div>
                            </div>
                            <div class="booking-info-item">
                                <div class="booking-info-icon">
                                    <i class="bi bi-clock"></i>
                                </div>
                                <div class="booking-info-content">
                                    <div class="booking-info-label">Time</div>
                                    <div class="booking-info-value">${booking.time}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">Additional Details</h6>
                        <div class="booking-info">
                            <div class="booking-info-item">
                                <div class="booking-info-icon">
                                    <i class="bi bi-hourglass"></i>
                                </div>
                                <div class="booking-info-content">
                                    <div class="booking-info-label">Duration</div>
                                    <div class="booking-info-value">${booking.duration}</div>
                                </div>
                            </div>
                            <div class="booking-info-item">
                                <div class="booking-info-icon">
                                    <i class="bi bi-tag"></i>
                                </div>
                                <div class="booking-info-content">
                                    <div class="booking-info-label">Price</div>
                                    <div class="booking-info-value">${booking.price}</div>
                                </div>
                            </div>
                            <div class="booking-info-item">
                                <div class="booking-info-icon">
                                    <i class="bi bi-geo-alt"></i>
                                </div>
                                <div class="booking-info-content">
                                    <div class="booking-info-label">Location</div>
                                    <div class="booking-info-value">${booking.location}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <h6 class="text-primary mb-3">Additional Information</h6>
                        <div class="alert alert-modern alert-info-modern">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Equipment:</strong> ${booking.equipment}<br>
                            <strong>Notes:</strong> ${booking.notes}
                        </div>
                    </div>
                </div>
            `;
            
            new bootstrap.Modal(document.getElementById('bookingModal')).show();
        }

        // Bundle breakdown data per booking
        const bundleBreakdownData = {
            'BK001': {
                bundleName: 'Basketball Court Package',
                bundleDesc: 'Full court access package with all necessary equipment and facilities.',
                items: [
                    { icon: 'bi-building', name: 'Court Reservation', detail: 'Court A – Indoor Sports Complex (2 hrs)', price: '₱15.00' },
                    { icon: 'bi-circle', name: 'Ball Rental', detail: 'Standard NBA-size basketball', price: '₱5.00' },
                    { icon: 'bi-lightning', name: 'Flood Lights', detail: 'Full court lighting (1 hr)', price: '₱5.00' }
                ],
                total: '₱25.00'
            },
            'BK002': {
                bundleName: 'Swimming Pool Package',
                bundleDesc: 'Relaxing pool access bundle including locker storage for your belongings.',
                items: [
                    { icon: 'bi-water', name: 'Pool Access', detail: 'Main Pool – Adult entry (1 hr)', price: '₱10.00' },
                    { icon: 'bi-lock', name: 'Locker Rental', detail: 'Secure locker for the session', price: '₱5.00' }
                ],
                total: '₱15.00'
            },
            'BK003': {
                bundleName: 'Fitness Gym Package',
                bundleDesc: 'Full gym access with professional instructor and all equipment included.',
                items: [
                    { icon: 'bi-person-arms-up', name: 'Gym Day Pass', detail: 'Full access to all gym equipment', price: '₱15.00' },
                    { icon: 'bi-wind', name: 'Towel Rental', detail: 'Fresh towel for the session', price: '₱5.00' }
                ],
                total: '₱20.00'
            },
            'BK004': {
                bundleName: 'Adventure Rides Package',
                bundleDesc: 'Thrilling ATV adventure bundle with complete safety gear included.',
                items: [
                    { icon: 'bi-truck', name: 'ATV Rental', detail: '4-wheel all-terrain vehicle (2 hrs)', price: '₱35.00' },
                    { icon: 'bi-shield-check', name: 'Safety Gear Bundle', detail: 'Helmet, knee pads & gloves', price: '₱10.00' }
                ],
                total: '₱45.00'
            }
        };

        let currentBundleId = null;

        function openBundleModal() {
            if (!currentBundleId) return;
            const data = bundleBreakdownData[currentBundleId] || bundleBreakdownData['BK001'];

            const rows = data.items.map(item => `
                <div class="d-flex align-items-start gap-3 py-3" style="border-bottom: 1px solid rgba(255,255,255,0.07);">
                    <div style="width: 42px; height: 42px; flex-shrink: 0; border-radius: 10px; background: rgba(157,255,0,0.1); border: 1px solid rgba(157,255,0,0.25); display: flex; align-items: center; justify-content: center;">
                        <i class="${item.icon}" style="color: #9dff00; font-size: 1.1rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold text-white" style="font-size: 0.95rem;">${item.name}</div>
                        <div style="color: #94a3b8; font-size: 0.82rem; margin-top: 2px;">${item.detail}</div>
                    </div>
                    <div class="fw-bold text-end" style="color: #00d4ff; font-size: 0.95rem; white-space: nowrap;">${item.price}</div>
                </div>
            `).join('');

            document.getElementById('bundleDetails').innerHTML = `
                <div class="mb-4 p-3 rounded" style="background: rgba(157,255,0,0.05); border: 1px solid rgba(157,255,0,0.2);">
                    <div class="fw-bold text-white mb-1" style="font-size: 1.05rem;">
                        <i class="bi bi-stars me-2" style="color: #9dff00;"></i>${data.bundleName}
                    </div>
                    <div style="color: #94a3b8; font-size: 0.875rem;">${data.bundleDesc}</div>
                </div>

                <h6 class="text-uppercase mb-3" style="color: #94a3b8; font-size: 0.75rem; letter-spacing: 0.08em;">What's Included</h6>

                <div>${rows}</div>

                <div class="d-flex justify-content-between align-items-center mt-4 pt-3" style="border-top: 1px solid rgba(255,255,255,0.1);">
                    <span class="fw-bold text-white" style="font-size: 1rem;">Total Amount</span>
                    <span class="fw-bold" style="font-size: 1.4rem; color: #9dff00;">${data.total}</span>
                </div>
            `;

            // Hide booking modal first, then show bundle modal
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

        function downloadTicket(bookingId) {
            showSuccess(`Ticket download would generate PDF for booking: ${bookingId}`);
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

        // Filter Functions
        function applyFilters() {
            const statusFilter = document.getElementById('statusFilter').value;
            const activityFilter = document.getElementById('activityFilter').value;
            const dateFilter = document.getElementById('dateFilter').value;
            
            const bookingCards = document.querySelectorAll('.booking-card');
            let visibleCount = 0;
            
            bookingCards.forEach(card => {
                let show = true;
                
                if (statusFilter && card.dataset.status !== statusFilter) {
                    show = false;
                }
                
                if (activityFilter && card.dataset.activity !== activityFilter) {
                    show = false;
                }
                
                if (show) {
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
            
            showSuccess(`Filters applied. Showing ${visibleCount} bookings.`);
        }

        function resetFilters() {
            document.getElementById('statusFilter').value = '';
            document.getElementById('activityFilter').value = '';
            document.getElementById('dateFilter').value = '';
            
            const bookingCards = document.querySelectorAll('.booking-card');
            bookingCards.forEach(card => {
                card.style.display = 'block';
            });
            
            document.getElementById('emptyState').style.display = 'none';
            document.getElementById('bookingsList').style.display = 'block';
            
            showSuccess('Filters reset successfully.');
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
    </script>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
