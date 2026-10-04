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
            <div class="chart-card overview-card">
                <div class="chart-header">
                    <h5 class="chart-title">Bookings Overview</h5>
                    <div class="chart-options">
                        <button type="button" class="chart-option active" data-range="week">Week</button>
                        <button type="button" class="chart-option" data-range="month">Month</button>
                        <button type="button" class="chart-option" data-range="year">Year</button>
                    </div>
                </div>
                <div class="chart-container">
                    <div class="overview-canvas-wrap">
                        <canvas id="bookingsOverviewChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-header">
                    <h5 class="chart-title">Bookings Status Overview</h5>
                </div>
                <style>
                    /* ── Bookings Overview: fill the card height (no empty space under the chart) ── */
                    .chart-card.overview-card {
                        display: flex;
                        flex-direction: column;
                    }
                    .chart-card.overview-card > .chart-container {
                        flex: 1 1 auto;
                        align-items: stretch;
                        min-height: 300px;
                    }
                    .overview-canvas-wrap {
                        position: relative;
                        flex: 1 1 auto;
                        min-width: 0;
                        align-self: stretch;
                    }
                    .overview-canvas-wrap canvas {
                        position: absolute;
                        top: 0;
                        left: 0;
                    }
                    /* ── Bookings Status Pie (scoped to this card) ── */
                    .chart-container.pie-container {
                        flex-direction: column;
                        align-items: stretch;
                        justify-content: flex-start;
                        gap: 1.1rem;
                        min-height: 0;
                        padding: 1.25rem 1.1rem 1.1rem;
                        position: relative;
                        overflow: hidden;
                        background:
                            radial-gradient(circle at 50% 0%, rgba(157, 255, 0, 0.07), transparent 60%),
                            rgba(8, 18, 14, 0.45);
                    }
                    .pie-stage {
                        position: relative;
                        width: 232px;
                        height: 232px;
                        max-width: 100%;
                        aspect-ratio: 1 / 1;
                        margin: 0 auto;
                        flex-shrink: 0;
                    }
                    .pie-legend {
                        display: grid;
                        gap: 0.5rem;
                        width: 100%;
                    }
                    .pie-legend-item {
                        --c: #9DFF00;
                        display: grid;
                        grid-template-columns: auto 1fr auto;
                        align-items: center;
                        column-gap: 0.65rem;
                        row-gap: 0.45rem;
                        padding: 0.6rem 0.8rem;
                        border-radius: 12px;
                        background: rgba(255, 255, 255, 0.03);
                        border: 1px solid rgba(255, 255, 255, 0.06);
                        cursor: default;
                        transition: border-color 0.25s ease, background 0.25s ease, transform 0.25s ease;
                    }
                    .pie-legend-item:hover {
                        border-color: var(--c);
                        background: rgba(255, 255, 255, 0.05);
                        transform: translateX(2px);
                    }
                    .pie-dot {
                        width: 10px;
                        height: 10px;
                        border-radius: 50%;
                        background: var(--c);
                        box-shadow: 0 0 10px var(--c);
                    }
                    .pie-name {
                        color: #d5e0da;
                        font-size: 0.8rem;
                        font-weight: 600;
                        letter-spacing: 0.02em;
                    }
                    .pie-figures {
                        display: flex;
                        align-items: baseline;
                        gap: 0.45rem;
                    }
                    .pie-figures strong {
                        color: #ffffff;
                        font-size: 0.95rem;
                        font-weight: 700;
                    }
                    .pie-figures em {
                        font-style: normal;
                        color: var(--c);
                        font-size: 0.72rem;
                        font-weight: 700;
                        min-width: 2.6em;
                        text-align: right;
                    }
                    .pie-bar {
                        grid-column: 1 / -1;
                        height: 4px;
                        border-radius: 4px;
                        background: rgba(255, 255, 255, 0.07);
                        overflow: hidden;
                    }
                    .pie-bar i {
                        display: block;
                        height: 100%;
                        border-radius: 4px;
                        background: linear-gradient(90deg, var(--c), rgba(255, 255, 255, 0.55));
                        box-shadow: 0 0 8px var(--c);
                    }
                </style>
                <div class="chart-container pie-container">
                    <div class="pie-stage">
                        <canvas id="bookingsPieChart"></canvas>
                    </div>
                    <div class="pie-legend" id="bookingsPieLegend">
                        <?php
                        $pieTotal = (int)$totalBookings;
                        $pieItems = [
                            ['Pending',  (int)$pendingBookings,  '#F5A623'],
                            ['Approved', (int)$approvedBookings, '#82D616'],
                            ['Rejected', (int)$rejectedBookings, '#EF4B55'],
                        ];
                        $pieOther = $pieTotal - ((int)$pendingBookings + (int)$approvedBookings + (int)$rejectedBookings);
                        if ($pieOther > 0) {
                            $pieItems[] = ['Other', $pieOther, '#6FA8DC'];
                        }
                        foreach ($pieItems as $pieIndex => $pieItem):
                            $piePct = $pieTotal > 0 ? round(($pieItem[1] / $pieTotal) * 100) : 0;
                        ?>
                        <div class="pie-legend-item" data-index="<?php echo $pieIndex; ?>" style="--c: <?php echo $pieItem[2]; ?>;">
                            <span class="pie-dot"></span>
                            <span class="pie-name"><?php echo $pieItem[0]; ?></span>
                            <span class="pie-figures"><strong><?php echo number_format($pieItem[1]); ?></strong><em><?php echo $piePct; ?>%</em></span>
                            <span class="pie-bar"><i style="width: <?php echo $piePct; ?>%;"></i></span>
                        </div>
                        <?php endforeach; ?>
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

        // ── Bookings Status Pie Chart ────────────────────────────────────────
        document.addEventListener('DOMContentLoaded', function () {
            const pieCanvas = document.getElementById('bookingsPieChart');
            if (!pieCanvas || typeof Chart === 'undefined') return;
            const pieCtx = pieCanvas.getContext('2d');

            const counts = [
                <?php echo (int)$pendingBookings; ?>,
                <?php echo (int)$approvedBookings; ?>,
                <?php echo (int)$rejectedBookings; ?>
            ];
            const total = <?php echo (int)$totalBookings; ?>;
            const otherCount = Math.max(0, total - (counts[0] + counts[1] + counts[2]));
            if (otherCount > 0) counts.push(otherCount); // completed / cancelled / etc.
            const approvalRate = total > 0 ? Math.round((counts[1] / total) * 100) : 0;
            const isEmpty = total === 0;

            // Slice palettes [light, base] – matches the dark / neon-green admin theme
            const palettes = [
                ['#FFD27A', '#F5A623'], // Pending
                ['#C6FF5C', '#82D616'], // Approved
                ['#FF8A90', '#E0343F'], // Rejected
                ['#A9CDF0', '#5B93C9']  // Other
            ];
            const sliceLabels = ['Pending', 'Approved', 'Rejected', 'Other'].slice(0, counts.length);

            // Pre-built gradients (canvas is 232px square)
            const sliceColors = palettes.slice(0, counts.length).map(function (p) {
                const g = pieCtx.createLinearGradient(0, 0, 232, 232);
                g.addColorStop(0, p[0]);
                g.addColorStop(1, p[1]);
                return g;
            });

            // Decorative layers: soft shadow, track, dashed halo, ticks, approval ring, centre text
            const pieDecor = {
                id: 'pieDecor',
                beforeDatasetsDraw: function (chart) {
                    const arc = chart.getDatasetMeta(0).data[0];
                    if (!arc) return;
                    const c = chart.ctx;
                    const mid = (arc.innerRadius + arc.outerRadius) / 2;
                    const thick = arc.outerRadius - arc.innerRadius;

                    // faint background track
                    c.save();
                    c.beginPath();
                    c.arc(arc.x, arc.y, mid, 0, Math.PI * 2);
                    c.lineWidth = thick;
                    c.strokeStyle = 'rgba(255, 255, 255, 0.035)';
                    c.stroke();
                    c.restore();

                    // drop shadow for the slices
                    c.save();
                    c.shadowColor = 'rgba(0, 0, 0, 0.55)';
                    c.shadowBlur = 14;
                    c.shadowOffsetY = 5;
                },
                afterDatasetsDraw: function (chart) {
                    const arc = chart.getDatasetMeta(0).data[0];
                    if (!arc) return;
                    const c = chart.ctx;
                    c.restore(); // end slice shadow

                    const cx = arc.x, cy = arc.y;
                    const outer = arc.outerRadius;
                    const inner = arc.innerRadius;
                    if (!(outer > 0) || inner < 24) return; // too small to decorate safely

                    // dashed halo
                    c.save();
                    c.beginPath();
                    c.arc(cx, cy, outer + 10, 0, Math.PI * 2);
                    c.setLineDash([2, 5]);
                    c.lineWidth = 1;
                    c.strokeStyle = 'rgba(157, 255, 0, 0.28)';
                    c.stroke();
                    c.restore();

                    // 12 tick marks (like a watch bezel)
                    c.save();
                    c.lineWidth = 1.5;
                    c.lineCap = 'round';
                    for (let t = 0; t < 12; t++) {
                        const a = (t / 12) * Math.PI * 2 - Math.PI / 2;
                        c.beginPath();
                        c.moveTo(cx + Math.cos(a) * (outer + 13), cy + Math.sin(a) * (outer + 13));
                        c.lineTo(cx + Math.cos(a) * (outer + 17), cy + Math.sin(a) * (outer + 17));
                        c.strokeStyle = t % 3 === 0 ? 'rgba(157, 255, 0, 0.55)' : 'rgba(255, 255, 255, 0.16)';
                        c.stroke();
                    }
                    c.restore();

                    // soft inner glow disc
                    const discR = inner - 6;
                    const glow = c.createRadialGradient(cx, cy, 0, cx, cy, discR);
                    glow.addColorStop(0, 'rgba(157, 255, 0, 0.10)');
                    glow.addColorStop(0.65, 'rgba(157, 255, 0, 0.03)');
                    glow.addColorStop(1, 'rgba(157, 255, 0, 0)');
                    c.save();
                    c.beginPath();
                    c.arc(cx, cy, discR, 0, Math.PI * 2);
                    c.fillStyle = glow;
                    c.fill();
                    c.lineWidth = 1;
                    c.strokeStyle = 'rgba(255, 255, 255, 0.07)';
                    c.stroke();
                    c.restore();

                    // approval-rate progress ring
                    const ringR = inner - 12;
                    c.save();
                    c.beginPath();
                    c.arc(cx, cy, ringR, 0, Math.PI * 2);
                    c.lineWidth = 3;
                    c.strokeStyle = 'rgba(255, 255, 255, 0.06)';
                    c.stroke();
                    if (approvalRate > 0) {
                        c.beginPath();
                        c.arc(cx, cy, ringR, -Math.PI / 2, -Math.PI / 2 + (approvalRate / 100) * Math.PI * 2);
                        c.lineCap = 'round';
                        c.lineWidth = 3;
                        c.strokeStyle = '#9DFF00';
                        c.shadowColor = 'rgba(157, 255, 0, 0.8)';
                        c.shadowBlur = 10;
                        c.stroke();
                    }
                    c.restore();

                    // centre text
                    const font = "'Inter', sans-serif";
                    c.save();
                    c.textAlign = 'center';
                    c.textBaseline = 'middle';

                    c.font = '800 36px ' + font;
                    c.fillStyle = '#ffffff';
                    c.shadowColor = 'rgba(157, 255, 0, 0.35)';
                    c.shadowBlur = 14;
                    c.fillText(String(total), cx, cy - 8);

                    c.shadowBlur = 0;
                    if ('letterSpacing' in c) c.letterSpacing = '1.4px';
                    c.font = '600 9.5px ' + font;
                    c.fillStyle = '#8C9992';
                    c.fillText('TOTAL BOOKINGS', cx, cy + 16);

                    c.font = '700 10.5px ' + font;
                    c.fillStyle = '#9DFF00';
                    c.fillText(approvalRate + '% APPROVED', cx, cy + 32);
                    c.restore();
                }
            };

            const pieChart = new Chart(pieCtx, {
                type: 'doughnut',
                data: {
                    labels: isEmpty ? ['No bookings'] : sliceLabels,
                    datasets: [{
                        data: isEmpty ? [1] : counts,
                        backgroundColor: isEmpty ? ['rgba(255, 255, 255, 0.05)'] : sliceColors,
                        hoverBackgroundColor: isEmpty ? ['rgba(255, 255, 255, 0.05)'] : sliceColors,
                        borderWidth: 0,
                        spacing: isEmpty ? 0 : 3,
                        borderRadius: 4,
                        hoverOffset: 4
                    }]
                },
                plugins: [pieDecor],
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '74%',
                    layout: { padding: 20 },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            enabled: !isEmpty,
                            backgroundColor: 'rgba(8, 18, 14, 0.92)',
                            titleColor: '#ffffff',
                            bodyColor: '#d5e0da',
                            titleFont: { family: "'Inter', sans-serif", size: 12, weight: '700' },
                            bodyFont: { family: "'Inter', sans-serif", size: 12, weight: '500' },
                            padding: 12,
                            boxPadding: 6,
                            usePointStyle: true,
                            borderColor: 'rgba(157, 255, 0, 0.25)',
                            borderWidth: 1,
                            cornerRadius: 10,
                            callbacks: {
                                label: function (context) {
                                    const value = context.parsed || 0;
                                    const pct = total > 0 ? Math.round((value / total) * 100) : 0;
                                    return ' ' + value + (value === 1 ? ' booking' : ' bookings') + ' · ' + pct + '%';
                                }
                            }
                        }
                    },
                    animation: {
                        animateScale: false,
                        animateRotate: true,
                        duration: 1200,
                        easing: 'easeOutQuart'
                    }
                }
            });

            // Hovering a legend row highlights its slice (and vice-versa via tooltip)
            document.querySelectorAll('#bookingsPieLegend .pie-legend-item').forEach(function (row) {
                const i = parseInt(row.dataset.index, 10);
                row.addEventListener('mouseenter', function () {
                    if (isEmpty) return;
                    const el = pieChart.getDatasetMeta(0).data[i];
                    if (!el) return;
                    const pos = el.tooltipPosition();
                    pieChart.setActiveElements([{ datasetIndex: 0, index: i }]);
                    pieChart.tooltip.setActiveElements([{ datasetIndex: 0, index: i }], { x: pos.x, y: pos.y });
                    pieChart.update();
                });
                row.addEventListener('mouseleave', function () {
                    if (isEmpty) return;
                    pieChart.setActiveElements([]);
                    pieChart.tooltip.setActiveElements([], { x: 0, y: 0 });
                    pieChart.update();
                });
            });
        });
    </script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>

