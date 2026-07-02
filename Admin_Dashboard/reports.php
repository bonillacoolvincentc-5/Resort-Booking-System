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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - YP4K Resort</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/resort-theme.css">
    <link rel="stylesheet" href="../css/dashboard-resort.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="components/sidebar.css">
    <link rel="stylesheet" href="components/navbar.css">
    
    <link rel="stylesheet" href="../css/admin-theme.css">
    <script src="components/admin-components.js" defer></script>
</head>
<body>
    <div id="sidebar-container"></div>
    <div id="navbar-container"></div>

    <div id="mainContent" class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title">Reports & Analytics</h1>
                <p class="text-muted">Comprehensive insights and performance metrics</p>
            </div>
            <div class="page-actions">
                <button class="btn-modern btn-secondary">
                    <i class="bi bi-calendar-range me-1"></i> Date Range
                </button>
                <button class="btn-modern btn-primary">
                    <i class="bi bi-download me-1"></i> Export Report
                </button>
            </div>
        </div>

        <div class="reports-grid">
            <div class="chart-card">
                <div class="chart-header">
                    <h5 class="chart-title">Revenue Analytics</h5>
                    <div class="chart-options">
                        <button class="chart-option active">Monthly</button>
                        <button class="chart-option">Weekly</button>
                        <button class="chart-option">Daily</button>
                    </div>
                </div>
                <div class="chart-container">
                    <div class="chart-placeholder">
                        <i class="bi bi-graph-up"></i>
                        <p>Revenue Trend Chart</p>
                    </div>
                </div>
            </div>

            <div>
                <div class="chart-card">
                    <div class="chart-header">
                        <h5 class="chart-title">Key Metrics</h5>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-info">
                                <div class="stat-label">Total Revenue</div>
                                <div class="stat-value">&#8369;124,567</div>
                            </div>
                            <div class="stat-icon">
                                <span style="font-weight: 800; font-style: normal; font-size: 1.4rem; line-height: 1;">&#8369;</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-info">
                                <div class="stat-label">Active Users</div>
                                <div class="stat-value">892</div>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-people"></i>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-info">
                                <div class="stat-label">Bookings</div>
                                <div class="stat-value">1,284</div>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-info">
                                <div class="stat-label">Growth Rate</div>
                                <div class="stat-value trend-up">+18.5%</div>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-graph-up-arrow"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="chart-card mt-3">
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
        </div>

        <div class="table-section mt-4">
            <div class="chart-header">
                <h5 class="chart-title">Recent Performance</h5>
                <a href="#" class="text-primary text-decoration-none">View All</a>
            </div>
            <table class="table-modern">
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Revenue</th>
                        <th>Bookings</th>
                        <th>Users</th>
                        <th>Growth</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>March 2026</td>
                        <td>&#8369;45,678</td>
                        <td>1,284</td>
                        <td>892</td>
                        <td class="trend-up">+18.5%</td>
                    </tr>
                    <tr>
                        <td>February 2026</td>
                        <td>&#8369;38,456</td>
                        <td>1,156</td>
                        <td>823</td>
                        <td class="trend-up">+12.3%</td>
                    </tr>
                    <tr>
                        <td>January 2026</td>
                        <td>&#8369;32,789</td>
                        <td>987</td>
                        <td>756</td>
                        <td class="trend-down">-5.2%</td>
                    </tr>
                    <tr>
                        <td>December 2025</td>
                        <td>&#8369;41,234</td>
                        <td>1,445</td>
                        <td>912</td>
                        <td class="trend-up">+8.7%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>

        // Chart option switching
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('chart-option')) {
                const options = e.target.parentElement.querySelectorAll('.chart-option');
                options.forEach(opt => opt.classList.remove('active'));
                e.target.classList.add('active');
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>



