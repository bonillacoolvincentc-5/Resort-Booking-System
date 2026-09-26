<?php
session_start();

// Auth guard — consistent with my-bookings.php and profile.php
if (!isset($_SESSION['user_id'])) {
    header('Location: ../Authentication/login.php');
    exit();
}

require_once '../phpAuthentication/db_config.php';

$userId    = $_SESSION['user_id'];
$userEmail = $_SESSION['user_email'];
$userName  = $_SESSION['user_name'];

// ── 1. Fetch full user record (avatar, verified status) ──────────────────────
$stmt = $conn->prepare("SELECT first_name, last_name, email, avatar, is_verified FROM users WHERE id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$userRow = $stmt->get_result()->fetch_assoc();
$stmt->close();

$firstName  = $userRow['first_name'] ?? explode(' ', $userName)[0];
$fullName   = trim(($userRow['first_name'] ?? '') . ' ' . ($userRow['last_name'] ?? '')) ?: $userName;
$isVerified = !empty($userRow['is_verified']);
$avatarPath = !empty($userRow['avatar']) ? htmlspecialchars($userRow['avatar']) : '';

// ── 2. Active bookings count (Confirmed or Pending) ──────────────────────────
$stmt = $conn->prepare("
    SELECT COUNT(*) as cnt
    FROM bookings b
    JOIN booking_status bs ON b.status_id = bs.id
    WHERE b.user_id = ? AND bs.name IN ('Confirmed','Pending')
");
$stmt->bind_param('i', $userId);
$stmt->execute();
$activeBookings = (int)($stmt->get_result()->fetch_assoc()['cnt'] ?? 0);
$stmt->close();

// ── 3. Available activities count ────────────────────────────────────────────
$result = $conn->query("SELECT COUNT(*) as cnt FROM activities WHERE is_active = 1");
$activitiesCount = (int)($result->fetch_assoc()['cnt'] ?? 0);

// ── 4. Total spent ───────────────────────────────────────────────────────────
$stmt = $conn->prepare("SELECT COALESCE(SUM(total_amount), 0) as total FROM bookings WHERE user_id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$totalSpent = (float)($stmt->get_result()->fetch_assoc()['total'] ?? 0);
$stmt->close();

// ── 5. Recent activity — last 5 bookings ─────────────────────────────────────
$stmt = $conn->prepare("
    SELECT
        CASE
            WHEN b.booking_type = 'activity' THEN a.name
            ELSE acc.name
        END as item_name,
        b.booking_type,
        b.check_in_date,
        b.check_in_time,
        b.created_at,
        bs.name  as status_name
    FROM bookings b
    LEFT JOIN booking_status bs  ON b.status_id       = bs.id
    LEFT JOIN activities     a   ON b.activity_id      = a.id
    LEFT JOIN accommodations acc ON b.accommodation_id = acc.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
    LIMIT 5
");
$stmt->bind_param('i', $userId);
$stmt->execute();
$recentBookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ── 6. Upcoming events — next 2 future confirmed/pending bookings ─────────────
$stmt = $conn->prepare("
    SELECT
        CASE
            WHEN b.booking_type = 'activity' THEN a.name
            ELSE acc.name
        END as item_name,
        b.check_in_date,
        b.check_in_time,
        bs.name as status_name
    FROM bookings b
    LEFT JOIN booking_status bs  ON b.status_id       = bs.id
    LEFT JOIN activities     a   ON b.activity_id      = a.id
    LEFT JOIN accommodations acc ON b.accommodation_id = acc.id
    WHERE b.user_id = ?
      AND b.check_in_date >= CURDATE()
      AND bs.name IN ('Confirmed','Pending')
    ORDER BY b.check_in_date ASC, b.check_in_time ASC
    LIMIT 2
");
$stmt->bind_param('i', $userId);
$stmt->execute();
$upcomingEvents = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ── Helpers ───────────────────────────────────────────────────────────────────
function statusBadgeClass(string $s): string {
    return match (strtolower($s)) {
        'confirmed' => 'bg-success',
        'pending'   => 'bg-warning text-dark',
        'cancelled' => 'bg-danger',
        'completed' => 'bg-primary',
        default     => 'bg-secondary',
    };
}
function bookingTypeIcon(string $t): string {
    return match ($t) {
        'activity'      => 'bi-lightning-charge',
        'accommodation' => 'bi-house-door',
        'bundle'        => 'bi-collection',
        default         => 'bi-calendar-check',
    };
}
function bookingIconColour(string $t): string {
    return match ($t) { 'activity' => 'primary', 'accommodation' => 'success', default => 'warning' };
}
function fmtBookingDate(string $d, ?string $ti): string {
    if (!$d) return '—';
    try {
        $dt  = new DateTime($d . ($ti ? ' '.$ti : ''));
        $now = new DateTime();
        $diff = (int)$now->diff($dt)->days;
        $past = $dt < $now;
        if ($diff === 0) return 'Today, '.$dt->format('g:i A');
        if ($diff === 1 && !$past) return 'Tomorrow, '.$dt->format('g:i A');
        if ($diff === 1 && $past)  return 'Yesterday, '.$dt->format('g:i A');
        return $dt->format('M j, Y');
    } catch (Exception) { return $d; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - YP4K Resort</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/resort-theme.css">
    <link rel="stylesheet" href="../css/dashboard-resort.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --bg-tertiary: #334155;
            --bg-card: #1e293b;
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --text-muted: #cbd5e1;
            --accent-primary: #3b82f6;
            --accent-secondary: #8b5cf6;
            --accent-success: #10b981;
            --accent-warning: #f59e0b;
            --accent-danger: #ef4444;
            --border-color: #334155;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
            color: var(--text-primary);
            min-height: 100vh;
            line-height: 1.6;
        }

        /* Modern Navigation */
        .navbar-modern {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
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

        /* Modern Cards */
        .card-modern {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .card-modern:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-xl);
        }

        .card-header-modern {
            background: transparent;
            border-bottom: 1px solid var(--border-color);
            padding: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .card-body-modern {
            padding: 1.5rem;
        }

        /* Stats Cards */
        .stat-card {
            background: linear-gradient(135deg, var(--bg-card) 0%, var(--bg-tertiary) 100%);
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
            background: linear-gradient(90deg, var(--accent-primary), var(--accent-secondary));
        }

        .stat-card:hover {
            transform: translateY(-4px);
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

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--text-primary), var(--text-secondary));
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-label {
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Welcome Section */
        .welcome-card {
            background: linear-gradient(135deg, var(--bg-card) 0%, var(--bg-tertiary) 100%);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        .welcome-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 0%, transparent 70%);
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .welcome-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--text-primary), var(--accent-primary));
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .welcome-text {
            color: var(--text-secondary);
            font-size: 1.1rem;
        }

        /* Modern Buttons */
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
        }

        .btn-primary-modern {
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            color: white;
        }

        .btn-primary-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }

        .btn-outline-modern {
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .btn-outline-modern:hover {
            background: var(--bg-tertiary);
            border-color: var(--accent-primary);
            color: var(--accent-primary);
        }

        .btn-ghost {
            background: transparent;
            border: 1px solid transparent;
            color: var(--text-secondary);
            padding: 0.5rem 1rem;
        }

        .btn-ghost:hover {
            background: rgba(59, 130, 246, 0.1);
            color: var(--accent-primary);
        }

        /* Profile Section */
        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 1rem;
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            color: white;
            box-shadow: var(--shadow-lg);
        }

        .profile-name {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }

        .profile-email {
            color: var(--text-muted);
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }

        /* Quick Actions */
        .action-grid {
            display: grid;
            gap: 0.75rem;
        }

        .action-item {
            padding: 1rem;
            background: rgba(59, 130, 246, 0.05);
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .action-item:hover {
            background: rgba(59, 130, 246, 0.1);
            border-color: var(--accent-primary);
            transform: translateY(-2px);
        }

        .action-item i {
            font-size: 1.25rem;
            color: var(--accent-primary);
            margin-bottom: 0.5rem;
            display: block;
        }

        .action-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        /* Alert Styles */
        .alert-modern {
            border-radius: 0.75rem;
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
        }

        .alert-success-modern {
            background: rgba(16, 185, 129, 0.1);
            color: var(--accent-success);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        /* Footer */
        .footer-modern {
            background: var(--bg-primary);
            border-top: 1px solid var(--border-color);
            padding: 2rem 0;
            margin-top: 3rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .welcome-title {
                font-size: 1.5rem;
            }
            
            .stat-number {
                font-size: 2rem;
            }
            
            .navbar-brand {
                font-size: 1.1rem;
            }
        }

        /* Loading Animation */
        .skeleton {
            background: linear-gradient(90deg, var(--bg-tertiary) 25%, var(--bg-card) 50%, var(--bg-tertiary) 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>
</head>
<body>
    <!-- Modern Navigation -->
    <?php include 'components/navigation.php'; ?>

    <!-- Main Content -->
    <div class="container-fluid px-4 py-4">
        <!-- Success Alert -->
        <div id="successAlert" class="alert alert-modern alert-success-modern alert-dismissible fade show d-none" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <span id="successMessage"></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="welcome-card">
                    <div class="position-relative">
                        <h1 class="welcome-title">
                            Welcome back, <?= htmlspecialchars($firstName) ?>!
                        </h1>
                        <p class="welcome-text">
                            Ready for your next adventure? You have <strong><?= $activeBookings ?> active <?= $activeBookings === 1 ? 'booking' : 'bookings' ?></strong> and <strong><?= $activitiesCount ?> <?= $activitiesCount === 1 ? 'activity' : 'activities' ?></strong> to explore.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="row g-4 mb-4">
            <div class="col-lg-4 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div class="stat-number" data-target="<?= $activeBookings ?>" data-prefix="">—</div>
                    <div class="stat-label">Active Bookings</div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <div class="stat-number" data-target="<?= $activitiesCount ?>" data-prefix="">—</div>
                    <div class="stat-label">Activities</div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="bi bi-credit-card"></i>
                    </div>
                    <div class="stat-number" data-target="<?= (int)$totalSpent ?>" data-prefix="₱">—</div>
                    <div class="stat-label">Total Spent</div>
                </div>
            </div>
        </div>

        <!-- Main Dashboard Grid -->
        <div class="row g-4">
            <!-- Recent Activity -->
            <div class="col-lg-8">
                <div class="card-modern">
                    <div class="card-header-modern">
                        <i class="bi bi-clock-history me-2"></i>Recent Activity
                    </div>
                    <div class="card-body-modern">
                        <div class="activity-list">
                            <?php if (empty($recentBookings)): ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="bi bi-calendar-x" style="font-size:2rem;opacity:0.4;"></i>
                                    <p class="mt-2 mb-0">No bookings yet. Start exploring!</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($recentBookings as $idx => $booking):
                                    $isLast     = $idx === count($recentBookings) - 1;
                                    $itemName   = htmlspecialchars($booking['item_name'] ?? 'Booking');
                                    $statusName = $booking['status_name'] ?? 'Unknown';
                                    $badgeClass = statusBadgeClass($statusName);
                                    $iconClass  = bookingTypeIcon($booking['booking_type'] ?? '');
                                    $iconColour = bookingIconColour($booking['booking_type'] ?? '');
                                    $dateLabel  = fmtBookingDate(
                                        $booking['check_in_date'] ?? $booking['created_at'] ?? '',
                                        $booking['check_in_time'] ?? null
                                    );
                                ?>
                                <div class="d-flex align-items-center justify-content-between py-3<?= $isLast ? '' : ' border-bottom border-secondary' ?>">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon <?= $iconColour ?> me-3" style="width:40px;height:40px;font-size:1rem;flex-shrink:0;">
                                            <i class="bi <?= $iconClass ?>"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?= $itemName ?></div>
                                            <div class="text-muted small"><?= htmlspecialchars($dateLabel) ?></div>
                                        </div>
                                    </div>
                                    <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $statusName))) ?></span>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div class="mt-4">
                            <a href="my-bookings.php" class="btn-modern btn-primary-modern">
                                <i class="bi bi-calendar3"></i>View All Bookings
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card-modern mt-4">
                    <div class="card-header-modern">
                        <i class="bi bi-lightning-charge me-2"></i>Quick Actions
                    </div>
                    <div class="card-body-modern">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="action-item" onclick="window.location.href='../index.html#booking'">
                                    <i class="bi bi-plus-circle"></i>
                                    <div class="action-label">New Booking</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="action-item" onclick="window.location.href='../index.html#activities'">
                                    <i class="bi bi-compass"></i>
                                    <div class="action-label">Explore Activities</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="action-item" onclick="window.location.href='../index.html#contact'">
                                    <i class="bi bi-headset"></i>
                                    <div class="action-label">Get Support</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Profile Card -->
                <div class="card-modern">
                    <div class="card-header-modern">
                        <i class="bi bi-person-circle me-2"></i>Profile Overview
                    </div>
                    <div class="card-body-modern text-center">
                        <div class="profile-avatar" style="overflow:hidden;">
                            <?php if ($avatarPath): ?>
                                <img src="<?= $avatarPath ?>" alt="Profile photo" style="width:100%;height:100%;object-fit:cover;" loading="lazy">
                            <?php else: ?>
                                <i class="bi bi-person-fill"></i>
                            <?php endif; ?>
                        </div>
                        <div class="profile-name"><?= htmlspecialchars($fullName) ?></div>
                        <div class="profile-email"><?= htmlspecialchars($userEmail) ?></div>
                        <a href="profile.php" class="btn-modern btn-primary-modern w-100">
                            <i class="bi bi-gear"></i>Manage Profile
                        </a>
                    </div>
                </div>

                <!-- Upcoming Events -->
                <div class="card-modern mt-4">
                    <div class="card-header-modern">
                        <i class="bi bi-calendar-event me-2"></i>Upcoming Events
                    </div>
                    <div class="card-body-modern">
                        <?php
                        $monthColors = ['bg-primary','bg-success','bg-warning','bg-danger','bg-info'];
                        if (empty($upcomingEvents)):
                        ?>
                            <div class="text-center py-3 text-muted">
                                <i class="bi bi-calendar3" style="font-size:1.75rem;opacity:0.4;"></i>
                                <p class="mt-2 mb-0 small">No upcoming events scheduled.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($upcomingEvents as $ei => $ev):
                                try {
                                    $evDt    = new DateTime($ev['check_in_date']);
                                    $evMonth = $evDt->format('M');
                                    $evDay   = $evDt->format('j');
                                } catch (Exception) { $evMonth = '—'; $evDay = '—'; }
                                $evName  = htmlspecialchars($ev['item_name'] ?? 'Event');
                                $evTime  = !empty($ev['check_in_time'])
                                    ? (new DateTime($ev['check_in_time']))->format('g:i A')
                                    : '';
                                $badgeCl = $monthColors[$ei % count($monthColors)];
                                $isLastEv = $ei === count($upcomingEvents) - 1;
                            ?>
                            <div class="event-item <?= $isLastEv ? '' : 'mb-3' ?>">
                                <div class="d-flex align-items-start">
                                    <div class="event-date me-3">
                                        <div class="text-center">
                                            <div class="badge <?= $badgeCl ?>"><?= strtoupper($evMonth) ?></div>
                                            <div class="fw-bold"><?= $evDay ?></div>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold"><?= $evName ?></div>
                                        <?php if ($evTime): ?>
                                            <div class="text-muted small"><?= $evTime ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Footer -->
    <footer class="footer-modern">
        <div class="container-fluid px-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="text-muted">
                        © 2026 YP4K Resort. All rights reserved.
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="d-flex gap-3 justify-content-md-end">
                        <a href="#" class="text-muted text-decoration-none hover:text-primary">
                            <i class="bi bi-shield-check me-1"></i>Privacy
                        </a>
                        <a href="#" class="text-muted text-decoration-none hover:text-primary">
                            <i class="bi bi-file-text me-1"></i>Terms
                        </a>
                        <a href="#" class="text-muted text-decoration-none hover:text-primary">
                            <i class="bi bi-envelope me-1"></i>Contact
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Check for success message from login
        function checkSuccessMessage() {
            const urlParams = new URLSearchParams(window.location.search);
            const success = urlParams.get('success');
            if (success) {
                const alert = document.getElementById('successAlert');
                document.getElementById('successMessage').textContent = decodeURIComponent(success);
                alert.classList.remove('d-none');
                
                // Remove query parameter from URL
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        }

        // Add smooth transitions
        document.addEventListener('DOMContentLoaded', function() {
            // Animate stats using data-target attributes set by PHP
            document.querySelectorAll('.stat-number[data-target]').forEach(el => {
                const target = parseInt(el.dataset.target, 10);
                const prefix = el.dataset.prefix || '';
                if (isNaN(target)) { el.textContent = prefix + '0'; return; }
                let current = 0;
                const steps    = 50;
                const increment = target / steps;
                el.textContent = prefix + '0';
                const timer = setInterval(() => {
                    current = Math.min(current + increment, target);
                    el.textContent = prefix + Math.floor(current).toLocaleString();
                    if (current >= target) clearInterval(timer);
                }, 30);
            });

            // Check for messages on page load
            checkSuccessMessage();
        });

        // Add hover effects to cards
        document.querySelectorAll('.card-modern, .stat-card, .action-item').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    </script>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
