<?php
session_start();

// Include admin configuration
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../Authentication/admin-login.php');
    exit();
}

// Include database configuration
admin_include('db_config.php');

// Get admin info
$adminName = $_SESSION['admin_name'];
$adminEmail = $_SESSION['admin_email'];

// Get database connection
global $conn;

// Fetch real statistics from database
$totalBookings = 0;
$pendingBookings = 0;
$approvedBookings = 0;
$rejectedBookings = 0;
$totalRevenue = 0;
$activeUsers = 0;

// Total bookings
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM bookings");
$stmt->execute();
$result = $stmt->get_result();
$totalBookings = $result->fetch_assoc()['count'];

// Pending bookings
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM bookings b JOIN booking_status bs ON b.status_id = bs.id WHERE bs.name = 'pending_review'");
$stmt->execute();
$result = $stmt->get_result();
$pendingBookings = $result->fetch_assoc()['count'];

// Approved bookings
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM bookings b JOIN booking_status bs ON b.status_id = bs.id WHERE bs.name = 'approved'");
$stmt->execute();
$result = $stmt->get_result();
$approvedBookings = $result->fetch_assoc()['count'];

// Rejected bookings
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM bookings b JOIN booking_status bs ON b.status_id = bs.id WHERE bs.name = 'rejected'");
$stmt->execute();
$result = $stmt->get_result();
$rejectedBookings = $result->fetch_assoc()['count'];

// Total revenue (sum of approved bookings)
$stmt = $conn->prepare("SELECT SUM(total_amount) as total FROM bookings b JOIN booking_status bs ON b.status_id = bs.id WHERE bs.name IN ('approved', 'completed')");
$stmt->execute();
$result = $stmt->get_result();
$totalRevenue = $result->fetch_assoc()['total'] ?? 0;

// Active users
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM users");
$stmt->execute();
$result = $stmt->get_result();
$activeUsers = $result->fetch_assoc()['count'];

// Fetch recent activity from database
$recentActivities = [];
$activityQuery = "
    (SELECT 
        'booking' as type,
        CONCAT('New booking: ', b.full_name, ' - ', COALESCE(a.name, acc.name, 'Unknown')) as description,
        b.created_at as created_at,
        b.id as reference_id
    FROM bookings b
    LEFT JOIN activities a ON b.activity_id = a.id
    LEFT JOIN accommodations acc ON b.accommodation_id = acc.id
    ORDER BY b.created_at DESC
    LIMIT 5)
    UNION ALL
    (SELECT 
        'user' as type,
        CONCAT('New user: ', u.first_name, ' ', u.last_name) as description,
        u.created_at as created_at,
        u.id as reference_id
    FROM users u
    ORDER BY u.created_at DESC
    LIMIT 5)
    ORDER BY created_at DESC
    LIMIT 10
";
$stmt = $conn->prepare($activityQuery);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $recentActivities[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - YP4K Resort</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Include Components -->
    <link rel="stylesheet" href="components/sidebar.css">
    <link rel="stylesheet" href="components/navbar.css">
    
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
            --gradient-success: linear-gradient(135deg, var(--accent-success), #059669);
            --gradient-warning: linear-gradient(135deg, var(--accent-warning), #d97706);
            --gradient-danger: linear-gradient(135deg, var(--accent-danger), #dc2626);
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

        /* Main Content */
        .main-content {
            margin-left: 260px;
            margin-top: 70px;
            padding: 2rem;
            min-height: calc(100vh - 70px);
        }

        /* Overview Cards */
        .overview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .overview-card {
            background: var(--bg-card);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .overview-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
            border-color: var(--accent-primary);
        }

        .overview-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
        }

        .overview-card.primary::before {
            background: var(--gradient-primary);
        }

        .overview-card.success::before {
            background: var(--gradient-success);
        }

        .overview-card.warning::before {
            background: var(--gradient-warning);
        }

        .overview-card.danger::before {
            background: var(--gradient-danger);
        }

        .overview-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .overview-icon {
            width: 50px;
            height: 50px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .overview-icon.primary {
            background: rgba(59, 130, 246, 0.1);
            color: var(--accent-primary);
        }

        .overview-icon.success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--accent-success);
        }

        .overview-icon.warning {
            background: rgba(245, 158, 11, 0.1);
            color: var(--accent-warning);
        }

        .overview-icon.danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--accent-danger);
        }

        .overview-trend {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .trend-up {
            color: var(--accent-success);
        }

        .trend-down {
            color: var(--accent-danger);
        }

        .overview-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--text-primary), var(--text-secondary));
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .overview-label {
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.9rem;
        }

        /* Charts Section */
        .charts-section {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .chart-card {
            background: var(--bg-card);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 1.5rem;
        }

        .chart-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .chart-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .chart-options {
            display: flex;
            gap: 0.5rem;
        }

        .chart-option {
            padding: 0.375rem 0.75rem;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
            color: var(--text-secondary);
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .chart-option.active,
        .chart-option:hover {
            background: var(--accent-primary);
            color: white;
            border-color: var(--accent-primary);
        }

        .chart-container {
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, 0.3);
            border-radius: 0.5rem;
            position: relative;
        }

        .chart-placeholder {
            text-align: center;
            color: var(--text-muted);
        }

        .chart-placeholder i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* Recent Activity */
        .activity-card {
            background: var(--bg-card);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 1.5rem;
        }

        .activity-list {
            max-height: 350px;
            overflow-y: auto;
        }

        .activity-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(51, 65, 85, 0.2);
            transition: all 0.3s ease;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-item:hover {
            background: rgba(59, 130, 246, 0.02);
            margin: 0 -1rem;
            padding: 1rem;
            border-radius: 0.5rem;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .activity-icon.booking {
            background: rgba(59, 130, 246, 0.1);
            color: var(--accent-primary);
        }

        .activity-icon.user {
            background: rgba(16, 185, 129, 0.1);
            color: var(--accent-success);
        }

        .activity-icon.payment {
            background: rgba(245, 158, 11, 0.1);
            color: var(--accent-warning);
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            color: var(--text-primary);
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .activity-desc {
            color: var(--text-secondary);
            font-size: 0.85rem;
            margin-bottom: 0.25rem;
        }

        .activity-time {
            color: var(--text-muted);
            font-size: 0.75rem;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .charts-section {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .overview-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .top-navbar {
                left: 0;
            }
        }

        @media (max-width: 576px) {
            .overview-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Include Components -->
    <div id="sidebar-container"></div>
    <div id="navbar-container"></div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-2">Dashboard Overview</h1>
                <p class="text-muted mb-0">Welcome back! Here's what's happening at YP4K Resort today.</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary">
                    <i class="bi bi-download me-2"></i>Export Report
                </button>
                <a href="../admin/logout.php" class="btn btn-logout">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </a>
            </div>
        </div>

        <!-- Overview Cards -->
        <div class="overview-grid">
            <div class="overview-card primary">
                <div class="overview-header">
                    <div class="overview-icon primary">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div class="overview-trend trend-up">
                        <i class="bi bi-arrow-up"></i>
                        <span>12%</span>
                    </div>
                </div>
                <div class="overview-value"><?php echo number_format($totalBookings); ?></div>
                <div class="overview-label">Total Bookings</div>
            </div>

            <div class="overview-card warning">
                <div class="overview-header">
                    <div class="overview-icon warning">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="overview-trend trend-down">
                        <i class="bi bi-arrow-down"></i>
                        <span>3%</span>
                    </div>
                </div>
                <div class="overview-value"><?php echo number_format($pendingBookings); ?></div>
                <div class="overview-label">Pending Reservations</div>
            </div>

            <div class="overview-card success">
                <div class="overview-header">
                    <div class="overview-icon success">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div class="overview-trend trend-up">
                        <i class="bi bi-arrow-up"></i>
                        <span>18%</span>
                    </div>
                </div>
                <div class="overview-value">₱<?php echo number_format($totalRevenue, 2); ?></div>
                <div class="overview-label">Revenue</div>
            </div>

            <div class="overview-card danger">
                <div class="overview-header">
                    <div class="overview-icon danger">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="overview-trend trend-up">
                        <i class="bi bi-arrow-up"></i>
                        <span>8%</span>
                    </div>
                </div>
                <div class="overview-value"><?php echo number_format($activeUsers); ?></div>
                <div class="overview-label">Active Users</div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-section">
            <div class="chart-card">
                <div class="chart-header">
                    <h5 class="chart-title">Revenue Overview</h5>
                    <div class="chart-options">
                        <button class="chart-option active">Week</button>
                        <button class="chart-option">Month</button>
                        <button class="chart-option">Year</button>
                    </div>
                </div>
                <div class="chart-container">
                    <div class="chart-placeholder">
                        <i class="bi bi-graph-up"></i>
                        <p>Revenue Chart</p>
                    </div>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-header">
                    <h5 class="chart-title">Activity Distribution</h5>
                </div>
                <div class="chart-container">
                    <div class="chart-placeholder">
                        <i class="bi bi-pie-chart"></i>
                        <p>Activity Pie Chart</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="activity-card">
            <div class="chart-header">
                <h5 class="chart-title">Recent Activity</h5>
                <a href="#" class="text-primary text-decoration-none">View All</a>
            </div>
            <div class="activity-list">
                <?php if (count($recentActivities) > 0): ?>
                    <?php foreach ($recentActivities as $activity): ?>
                        <?php
                            $iconClass = $activity['type'] === 'booking' ? 'booking' : 'user';
                            $icon = $activity['type'] === 'booking' ? 'bi-calendar-check' : 'bi-person-plus';
                            $title = $activity['type'] === 'booking' ? 'New Booking' : 'New User Registration';
                            
                            // Calculate time ago
                            $timeAgo = time() - strtotime($activity['created_at']);
                            if ($timeAgo < 60) {
                                $timeText = $timeAgo . ' seconds ago';
                            } elseif ($timeAgo < 3600) {
                                $timeText = floor($timeAgo / 60) . ' minutes ago';
                            } elseif ($timeAgo < 86400) {
                                $timeText = floor($timeAgo / 3600) . ' hours ago';
                            } else {
                                $timeText = floor($timeAgo / 86400) . ' days ago';
                            }
                        ?>
                        <div class="activity-item">
                            <div class="activity-icon <?php echo $iconClass; ?>">
                                <i class="bi <?php echo $icon; ?>"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title"><?php echo $title; ?></div>
                                <div class="activity-desc"><?php echo htmlspecialchars($activity['description']); ?></div>
                                <div class="activity-time"><?php echo $timeText; ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 2rem;">
                        <i class="bi bi-inbox" style="font-size: 2rem; color: var(--text-muted);"></i>
                        <p style="color: var(--text-secondary); margin-top: 0.5rem;">No recent activity</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Load components
        fetch('components/sidebar.html')
            .then(response => response.text())
            .then(html => {
                document.getElementById('sidebar-container').innerHTML = html;
                // Set active navigation after sidebar loads
                setTimeout(setActiveNavigation, 50);
            });

        fetch('components/navbar.html')
            .then(response => response.text())
            .then(html => {
                document.getElementById('navbar-container').innerHTML = html;
            });

        // Chart option switching
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('chart-option')) {
                const options = e.target.parentElement.querySelectorAll('.chart-option');
                options.forEach(opt => opt.classList.remove('active'));
                e.target.classList.add('active');
            }
        });

        // Refresh data
        document.addEventListener('click', function(e) {
            if (e.target.closest('button')?.textContent?.includes('Refresh')) {
                // Simulate data refresh
                const cards = document.querySelectorAll('.overview-value');
                cards.forEach(card => {
                    card.style.opacity = '0.5';
                    setTimeout(() => {
                        card.style.opacity = '1';
                    }, 500);
                });
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
