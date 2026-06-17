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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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

        .main-content {
            margin-left: 260px;
            margin-top: 70px;
            padding: 2rem;
            min-height: calc(100vh - 70px);
        }

        .reports-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
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
            justify-content: space-between;
            align-items: center;
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

        .stats-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .stat-card {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            background: rgba(59, 130, 246, 0.1);
            border-color: var(--accent-primary);
        }

        .stat-info {
            display: flex;
            flex-direction: column;
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: 0.85rem;
            margin-bottom: 0.25rem;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
        }

        .table-section {
            background: var(--bg-card);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            overflow: hidden;
        }

        .table-modern {
            width: 100%;
            border-collapse: collapse;
        }

        .table-modern thead {
            background: var(--bg-tertiary);
        }

        .table-modern th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--border-color);
        }

        .table-modern td {
            padding: 1rem;
            color: var(--text-secondary);
            font-size: 0.9rem;
            border-bottom: 1px solid rgba(51, 65, 85, 0.2);
        }

        .table-modern tbody tr {
            transition: all 0.3s ease;
        }

        .table-modern tbody tr:hover {
            background: rgba(59, 130, 246, 0.05);
        }

        .trend-up {
            color: var(--accent-success);
        }

        .trend-down {
            color: var(--accent-danger);
        }

        @media (max-width: 1200px) {
            .reports-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div id="sidebar-container"></div>
    <div id="navbar-container"></div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-2">Reports & Analytics</h1>
                <p class="text-muted mb-0">Comprehensive insights and performance metrics</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary">
                    <i class="bi bi-calendar-range me-2"></i>Date Range
                </button>
                <button class="btn btn-primary">
                    <i class="bi bi-download me-2"></i>Export Report
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
                                <div class="stat-value">$124,567</div>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-currency-dollar"></i>
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
                        <td>$45,678</td>
                        <td>1,284</td>
                        <td>892</td>
                        <td class="trend-up">+18.5%</td>
                    </tr>
                    <tr>
                        <td>February 2026</td>
                        <td>$38,456</td>
                        <td>1,156</td>
                        <td>823</td>
                        <td class="trend-up">+12.3%</td>
                    </tr>
                    <tr>
                        <td>January 2026</td>
                        <td>$32,789</td>
                        <td>987</td>
                        <td>756</td>
                        <td class="trend-down">-5.2%</td>
                    </tr>
                    <tr>
                        <td>December 2025</td>
                        <td>$41,234</td>
                        <td>1,445</td>
                        <td>912</td>
                        <td class="trend-up">+8.7%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
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
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
