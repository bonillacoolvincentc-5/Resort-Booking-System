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
                <h1 class="page-title">Payment Management</h1>
                <p class="text-muted">Track all transactions and payment history</p>
            </div>
            <div class="page-actions">
                <button class="btn-modern btn-secondary">
                    <i class="bi bi-download me-1"></i> Export
                </button>
                <button class="btn-modern btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Process Refund
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
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>



