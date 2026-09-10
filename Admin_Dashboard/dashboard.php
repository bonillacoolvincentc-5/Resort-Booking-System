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
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/resort-theme.css">
    <link rel="stylesheet" href="../css/dashboard-resort.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Include Components -->
    <link rel="stylesheet" href="components/sidebar.css">
    <link rel="stylesheet" href="components/navbar.css">
    <link rel="stylesheet" href="../css/admin-theme.css">
    <script src="components/admin-components.js" defer></script>
</head>
<body>
    <!-- Include Components -->
    <div id="sidebar-container"></div>
    <div id="navbar-container"></div>

    <!-- Main Content -->
    <div id="mainContent" class="main-content">
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
                        <span style="font-weight: 800; font-style: normal; font-size: 1.4rem; line-height: 1;">&#8369;</span>
                    </div>
                    <div class="overview-trend trend-up">
                        <i class="bi bi-arrow-up"></i>
                        <span>18%</span>
                    </div>
                </div>
                <div class="overview-value">&#8369;<?php echo number_format($totalRevenue, 2); ?></div>
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
                    <h5 class="chart-title">Bookings Overview</h5>
                    <div class="chart-options">
                        <button type="button" class="chart-option active" data-range="week">Week</button>
                        <button type="button" class="chart-option" data-range="month">Month</button>
                        <button type="button" class="chart-option" data-range="year">Year</button>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="bookingsOverviewChart"></canvas>
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

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        // ── Bookings Overview chart ──────────────────────────────────────────
        let bookingsOverviewChart = null;

        function renderBookingsOverview(labels, data) {
            const canvas = document.getElementById('bookingsOverviewChart');
            if (!canvas || typeof Chart === 'undefined') return;
            const ctx = canvas.getContext('2d');
            if (bookingsOverviewChart) bookingsOverviewChart.destroy();
            const areaGradient = ctx.createLinearGradient(0, 0, 0, canvas.offsetHeight || 220);
            areaGradient.addColorStop(0, 'rgba(130, 214, 22, 0.35)');
            areaGradient.addColorStop(1, 'rgba(130, 214, 22, 0.02)');
            bookingsOverviewChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Bookings',
                        data: data,
                        borderColor: '#82d616',
                        backgroundColor: areaGradient,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#82d616',
                        pointBorderColor: '#82d616'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            grid: {
                                color: 'rgba(92, 107, 99, 0.18)',
                                borderDash: [4, 4],
                                drawBorder: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(92, 107, 99, 0.22)',
                                borderDash: [4, 4],
                                drawBorder: false
                            },
                            ticks: {
                                precision: 0,
                                callback: function (value) {
                                    return value;
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: { display: true },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const value = context.parsed.y ?? 0;
                                    return 'Bookings: ' + value;
                                }
                            }
                        }
                    }
                }
            });
        }

        async function loadBookingsOverview(range) {
            try {
                const res = await fetch('get_bookings_overview.php?range=' + encodeURIComponent(range));
                const json = await res.json();
                if (json.success) {
                    renderBookingsOverview(json.labels || [], json.data || []);
                }
            } catch (err) {
                console.error('Failed to load bookings overview', err);
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            loadBookingsOverview('week');
        });

        // ── Dashboard-specific: chart option switching ───────────────────────
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('chart-option')) {
                const options = e.target.parentElement.querySelectorAll('.chart-option');
                options.forEach(opt => opt.classList.remove('active'));
                e.target.classList.add('active');
                const range = e.target.dataset.range || 'week';
                loadBookingsOverview(range);
            }
        });

        // ── Dashboard-specific: refresh data animation ───────────────────────
        document.addEventListener('click', function(e) {
            if (e.target.closest('button')?.textContent?.includes('Refresh')) {
                const cards = document.querySelectorAll('.overview-value');
                cards.forEach(card => {
                    card.style.opacity = '0.5';
                    setTimeout(() => { card.style.opacity = '1'; }, 500);
                });
            }
        });
    </script>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>

