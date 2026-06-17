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
    <title>Payment Management - YP4K Resort</title>
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

        .overview-cards {
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
            text-align: center;
            transition: all 0.3s ease;
        }

        .overview-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .overview-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin: 0 auto 1rem;
        }

        .overview-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .overview-label {
            color: var(--text-secondary);
            font-weight: 500;
        }

        .payments-table {
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

        .payment-id {
            font-weight: 600;
            color: var(--accent-primary);
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .status-completed {
            background: rgba(16, 185, 129, 0.2);
            color: var(--accent-success);
        }

        .status-pending {
            background: rgba(245, 158, 11, 0.2);
            color: var(--accent-warning);
        }

        .status-failed {
            background: rgba(239, 68, 68, 0.2);
            color: var(--accent-danger);
        }

        .amount {
            font-weight: 700;
            color: var(--accent-success);
        }

        .amount.pending {
            color: var(--accent-warning);
        }

        .amount.failed {
            color: var(--accent-danger);
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .overview-cards {
                grid-template-columns: repeat(2, 1fr);
            }

            .table-modern {
                font-size: 0.8rem;
            }

            .table-modern th,
            .table-modern td {
                padding: 0.5rem;
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
                <h1 class="h3 mb-2">Payment Management</h1>
                <p class="text-muted mb-0">Track all transactions and payment history</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary">
                    <i class="bi bi-download me-2"></i>Export
                </button>
                <button class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Process Refund
                </button>
            </div>
        </div>

        <div class="overview-cards">
            <div class="overview-card">
                <div class="overview-icon">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div class="overview-value">$45,678</div>
                <div class="overview-label">Total Revenue</div>
            </div>

            <div class="overview-card">
                <div class="overview-icon">
                    <i class="bi bi-credit-card"></i>
                </div>
                <div class="overview-value">234</div>
                <div class="overview-label">Transactions</div>
            </div>

            <div class="overview-card">
                <div class="overview-icon">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="overview-value">12</div>
                <div class="overview-label">Pending</div>
            </div>

            <div class="overview-card">
                <div class="overview-icon">
                    <i class="bi bi-graph-up"></i>
                </div>
                <div class="overview-value">+18%</div>
                <div class="overview-label">Growth</div>
            </div>
        </div>

        <div class="payments-table">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="payment-id">#TRX001</td>
                        <td>John Doe</td>
                        <td class="amount">$25.00</td>
                        <td>Credit Card</td>
                        <td>Mar 21, 2026</td>
                        <td><span class="status-badge status-completed">Completed</span></td>
                    </tr>
                    <tr>
                        <td class="payment-id">#TRX002</td>
                        <td>Jane Smith</td>
                        <td class="amount">$15.00</td>
                        <td>PayPal</td>
                        <td>Mar 21, 2026</td>
                        <td><span class="status-badge status-completed">Completed</span></td>
                    </tr>
                    <tr>
                        <td class="payment-id">#TRX003</td>
                        <td>Mike Johnson</td>
                        <td class="amount pending">$45.00</td>
                        <td>Bank Transfer</td>
                        <td>Mar 21, 2026</td>
                        <td><span class="status-badge status-pending">Pending</span></td>
                    </tr>
                    <tr>
                        <td class="payment-id">#TRX004</td>
                        <td>Sarah Wilson</td>
                        <td class="amount">$20.00</td>
                        <td>Credit Card</td>
                        <td>Mar 20, 2026</td>
                        <td><span class="status-badge status-completed">Completed</span></td>
                    </tr>
                    <tr>
                        <td class="payment-id">#TRX005</td>
                        <td>Robert Chen</td>
                        <td class="amount failed">$30.00</td>
                        <td>Credit Card</td>
                        <td>Mar 20, 2026</td>
                        <td><span class="status-badge status-failed">Failed</span></td>
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
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
