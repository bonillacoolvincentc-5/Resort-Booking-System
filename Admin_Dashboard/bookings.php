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

// Fetch real bookings from database
$statusFilter = $_GET['status'] ?? null;

$query = "
    SELECT 
        b.id,
        b.booking_reference,
        b.booking_type,
        b.activity_id,
        b.accommodation_id,
        b.bundle_id,
        b.user_id,
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
        b.payment_method,
        b.created_at,
        bs.name as status_name,
        bs.description as status_description,
        bs.color as status_color,
        CONCAT(u.first_name, ' ', COALESCE(u.middle_name, ''), ' ', u.last_name) as user_name,
        u.email as user_email,
        u.phone_number as user_phone,
        CASE 
            WHEN b.booking_type = 'activity' THEN a.name
            WHEN b.booking_type = 'accommodation' THEN acc.name
            WHEN b.booking_type = 'bundle' THEN bun.name
            ELSE acc.name
        END as item_name,
        CASE 
            WHEN b.booking_type = 'activity' THEN a.icon
            ELSE NULL
        END as item_icon,
        pr.file_path as receipt_path
    FROM bookings b
    LEFT JOIN booking_status bs ON b.status_id = bs.id
    LEFT JOIN users u ON b.user_id = u.id
    LEFT JOIN activities a ON b.activity_id = a.id
    LEFT JOIN accommodations acc ON b.accommodation_id = acc.id
    LEFT JOIN bundles bun ON b.bundle_id = bun.id
    LEFT JOIN payment_receipts pr ON b.id = pr.booking_id
    WHERE 1=1
";

$params = [];
$types = '';

if ($statusFilter) {
    $query .= " AND bs.name = ?";
    $params[] = $statusFilter;
    $types .= 's';
}

$query .= " ORDER BY b.created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$bookings = [];
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}

// Fetch active users for dropdown
$usersRes = $conn->query("SELECT id, first_name, last_name, email FROM users ORDER BY first_name ASC, last_name ASC");
$allUsers = [];
while ($row = $usersRes->fetch_assoc()) {
    $allUsers[] = $row;
}

// Fetch activities
$actsRes = $conn->query("SELECT id, name, price_per_hour, capacity FROM activities WHERE is_active = 1 ORDER BY name ASC");
$activitiesList = [];
while ($row = $actsRes->fetch_assoc()) {
    $activitiesList[] = $row;
}

// Fetch accommodations
$accsRes = $conn->query("SELECT id, name, type, price_per_night, capacity FROM accommodations WHERE is_active = 1 ORDER BY name ASC");
$accommodationsList = [];
while ($row = $accsRes->fetch_assoc()) {
    $accommodationsList[] = $row;
}

// Fetch bundles
$bundlesRes = $conn->query("SELECT id, name, price, capacity FROM bundles WHERE is_active = 1 ORDER BY name ASC");
$bundlesList = [];
while ($row = $bundlesRes->fetch_assoc()) {
    $bundlesList[] = $row;
}

// Fetch booking statuses
$statusRes = $conn->query("SELECT id, name, description FROM booking_status WHERE is_active = 1 ORDER BY id ASC");
$statusesList = [];
while ($row = $statusRes->fetch_assoc()) {
    $statusesList[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings Management - YP4K Resort</title>
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

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .page-actions {
            display: flex;
            gap: 1rem;
        }

        .btn-modern {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            border: none;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }

        .btn-secondary {
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background: var(--bg-secondary);
            border-color: var(--accent-primary);
        }

        /* Filters Section */
        .filters-card {
            background: var(--bg-card);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-label {
            color: var(--text-primary);
            font-weight: 500;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .filter-input {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .filter-input:focus {
            background: var(--bg-tertiary);
            border-color: var(--accent-primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Bookings Table */
        .bookings-card {
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

        .customer-name {
            font-weight: 600;
            color: var(--text-primary);
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .status-pending {
            background: rgba(245, 158, 11, 0.2);
            color: var(--accent-warning);
        }

        .status-approved {
            background: rgba(16, 185, 129, 0.2);
            color: var(--accent-success);
        }

        .status-rejected {
            background: rgba(239, 68, 68, 0.2);
            color: var(--accent-danger);
        }

        .status-completed {
            background: rgba(59, 130, 246, 0.2);
            color: var(--accent-primary);
        }

        .status-cancelled {
            background: rgba(100, 116, 139, 0.2);
            color: var(--text-muted);
        }

        .status-pending_review {
            background: rgba(245, 158, 11, 0.2);
            color: var(--accent-warning);
        }

        .payment-method {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }

        .payment-icon {
            width: 24px;
            height: 24px;
            border-radius: 0.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            color: white;
        }

        .gcash-icon {
            background: linear-gradient(135deg, #00d4ff, #0099cc);
        }

        .cash-icon {
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-action {
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-approve {
            background: rgba(16, 185, 129, 0.1);
            color: var(--accent-success);
        }

        .btn-approve:hover {
            background: var(--accent-success);
            color: white;
        }

        .btn-reject {
            background: rgba(239, 68, 68, 0.1);
            color: var(--accent-danger);
        }

        .btn-reject:hover {
            background: var(--accent-danger);
            color: white;
        }

        .btn-edit {
            background: rgba(59, 130, 246, 0.1);
            color: var(--accent-primary);
        }

        .btn-edit:hover {
            background: var(--accent-primary);
            color: white;
        }

        /* Issue #17 fix — .btn-view was missing */
        .btn-view {
            background: rgba(139, 92, 246, 0.1);
            color: var(--accent-secondary);
        }

        .btn-view:hover {
            background: var(--accent-secondary);
            color: white;
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

        .form-group-modern {
            margin-bottom: 1.5rem;
        }

        .form-label-modern {
            color: var(--text-primary);
            font-weight: 500;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            display: block;
        }

        .form-control-modern {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            width: 100%;
        }

        .form-control-modern:focus {
            background: var(--bg-tertiary);
            border-color: var(--accent-primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Pagination */
        .pagination-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-top: 1px solid var(--border-color);
        }

        .pagination-info {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .pagination-controls {
            display: flex;
            gap: 0.5rem;
        }

        .page-btn {
            padding: 0.5rem 0.75rem;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            border-radius: 0.375rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .page-btn:hover,
        .page-btn.active {
            background: var(--accent-primary);
            color: white;
            border-color: var(--accent-primary);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .filters-grid {
                grid-template-columns: 1fr;
            }

            .table-modern {
                font-size: 0.8rem;
            }

            .table-modern th,
            .table-modern td {
                padding: 0.5rem;
            }
        }

        /* ── STACKING CONTEXT FIX ──────────────────────────────────────────
           backdrop-filter on .filters-card and .bookings-card creates new
           stacking contexts that trap Bootstrap modals. Force modal z-indices
           high enough to escape any stacking context.
        ──────────────────────────────────────────────────────────────────── */
        .modal { z-index: 10500 !important; }
        .modal-backdrop { z-index: 10400 !important; }
        .modal-dialog { z-index: 10501 !important; position: relative !important; }
    </style>
</head>
<body>
    <!-- Include Components -->
    <div id="sidebar-container"></div>
    <div id="navbar-container"></div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title">Resort Bookings Management</h1>
                <p class="text-muted">Manage and approve resort bookings</p>
            </div>
            <div class="page-actions">
                <button class="btn-modern btn-secondary">
                    <i class="bi bi-download"></i>
                    Export
                </button>
                <button class="btn-modern btn-primary" onclick="openNewBookingModal()">
                    <i class="bi bi-plus-circle"></i>
                    New Booking
                </button>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="filters-card">
            <div class="filters-grid">
                <div class="filter-group">
                    <label class="filter-label">Search Customer</label>
                    <input type="text" class="filter-input" placeholder="Search by customer name..." id="searchInput">
                </div>
                <div class="filter-group">
                    <label class="filter-label">Status</label>
                    <select class="filter-input" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Payment Method</label>
                    <select class="filter-input" id="paymentFilter">
                        <option value="">All Methods</option>
                        <option value="gcash">GCash</option>
                        <option value="cash">Cash</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Date Range</label>
                    <input type="date" class="filter-input" id="dateFilter">
                </div>
            </div>
        </div>

        <!-- Bookings Table -->
        <div class="bookings-card">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th>Customer Name</th>
                        <th>Date</th>
                        <th>Activity/Facility</th>
                        <th>Payment Method</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="bookingsTableBody">
                    <?php if (count($bookings) > 0): ?>
                        <?php foreach ($bookings as $booking): ?>
                            <tr data-booking-id="<?php echo $booking['id']; ?>">
                                <td class="customer-name"><?php echo htmlspecialchars($booking['full_name']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($booking['check_in_date'])); ?></td>
                                <td><?php echo htmlspecialchars($booking['item_name']); ?></td>
                                <td>
                                    <div class="payment-method">
                                        <div class="payment-icon <?php echo strtolower($booking['payment_method']); ?>-icon">
                                            <i class="bi bi-<?php echo $booking['payment_method'] === 'gcash' ? 'credit-card' : 'cash'; ?>"></i>
                                        </div>
                                        <span><?php echo ucfirst($booking['payment_method']); ?></span>
                                    </div>
                                </td>
                                <td><span class="status-badge status-<?php echo $booking['status_name']; ?>"><?php echo ucfirst(str_replace('_', ' ', $booking['status_name'])); ?></span></td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($booking['status_name'] === 'pending_review'): ?>
                                            <button class="btn-action btn-approve" onclick="approveBooking(<?php echo $booking['id']; ?>)">
                                                <i class="bi bi-check-circle"></i>
                                                Approve
                                            </button>
                                            <button class="btn-action btn-reject" onclick="rejectBooking(<?php echo $booking['id']; ?>)">
                                                <i class="bi bi-x-circle"></i>
                                                Reject
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn-action btn-view" onclick="viewBooking(<?php echo $booking['id']; ?>)">
                                            <i class="bi bi-eye"></i>
                                            View
                                        </button>
                                        <button class="btn-action btn-edit" onclick="editBooking(<?php echo $booking['id']; ?>)">
                                            <i class="bi bi-pencil"></i>
                                            Edit
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem;">
                                <i class="bi bi-inbox" style="font-size: 2rem; color: var(--text-muted);"></i>
                                <p style="color: var(--text-secondary); margin-top: 0.5rem;">No bookings found</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="pagination-section">
                <div class="pagination-info">
                    Showing <?php echo count($bookings); ?> bookings
                </div>
                <div class="pagination-controls">
                    <button class="page-btn">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button class="page-btn active">1</button>
                    <button class="page-btn">2</button>
                    <button class="page-btn">3</button>
                    <button class="page-btn">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Booking Modal -->
    <div class="modal fade" id="editBookingModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modal-content-modern">
                <div class="modal-header modal-header-modern">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil me-2"></i>Edit Booking Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body modal-body-modern">
                    <form id="editBookingForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Customer Name</label>
                                    <input type="text" class="form-control-modern" id="editCustomerName" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Date</label>
                                    <input type="date" class="form-control-modern" id="editDate" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Activity/Facility</label>
                                    <select class="form-control-modern" id="editActivity" required>
                                        <option value="basketball">Basketball Court</option>
                                        <option value="swimming">Swimming Pool</option>
                                        <option value="gym">Fitness Gym</option>
                                        <option value="adventure">Adventure Rides</option>
                                        <option value="playground">Kids Playground</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Payment Method</label>
                                    <select class="form-control-modern" id="editPaymentMethod" required>
                                        <option value="gcash">GCash</option>
                                        <option value="cash">Cash</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Time</label>
                                    <input type="time" class="form-control-modern" id="editTime" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Duration</label>
                                    <select class="form-control-modern" id="editDuration" required>
                                        <option value="1">1 hour</option>
                                        <option value="2">2 hours</option>
                                        <option value="3">3 hours</option>
                                        <option value="4">4 hours</option>
                                    </select>
                                </div>
                            </div>
                        <div class="form-group-modern">
                            <label class="form-label-modern">Special Requests</label>
                            <textarea class="form-control-modern" id="editSpecialRequests" rows="3" placeholder="Any special requirements..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer modal-footer-modern">
                    <button type="button" class="btn-modern btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn-modern btn-primary" onclick="saveBookingChanges()">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- New Booking Modal -->
    <div class="modal fade" id="newBookingModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modal-content-modern">
                <div class="modal-header modal-header-modern">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i>Create New Booking
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body modal-body-modern">
                    <form id="newBookingForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Select Customer <span class="text-danger">*</span></label>
                                    <select class="form-control-modern" id="newCustomerId" required>
                                        <option value="">-- Choose Customer --</option>
                                        <?php foreach ($allUsers as $u): ?>
                                            <option value="<?php echo $u['id']; ?>">
                                                <?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name'] . ' (' . $u['email'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Booking Type <span class="text-danger">*</span></label>
                                    <select class="form-control-modern" id="newBookingType" onchange="onBookingTypeChange()" required>
                                        <option value="">-- Choose Type --</option>
                                        <option value="activity">Activity</option>
                                        <option value="accommodation">Accommodation</option>
                                        <option value="bundle">Bundle</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Select Item <span class="text-danger">*</span></label>
                                    <select class="form-control-modern" id="newItemId" onchange="onItemChange()" required disabled>
                                        <option value="">-- Select Booking Type First --</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Check-in Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control-modern" id="newCheckInDate" required>
                                </div>
                            </div>
                        </div>

                        <!-- Check-out Date (For accommodations and bundles) -->
                        <div class="row" id="newCheckoutDateRow" style="display: none;">
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Check-out Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control-modern" id="newCheckOutDate">
                                </div>
                            </div>
                            <div class="col-md-6"></div>
                        </div>

                        <!-- Time fields (For activities) -->
                        <div class="row" id="newTimeFieldsRow" style="display: none;">
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Time-In <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control-modern" id="newCheckInTime">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Time-Out <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control-modern" id="newCheckOutTime">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Number of Guests <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control-modern" id="newNumberOfGuests" min="1" value="1" required>
                                    <small class="text-muted" id="newItemCapacityHint"></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Booking Status <span class="text-danger">*</span></label>
                                    <select class="form-control-modern" id="newStatusId" required>
                                        <?php foreach ($statusesList as $s): ?>
                                            <option value="<?php echo $s['id']; ?>" <?php echo $s['name'] === 'pending_review' ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $s['name']))); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Payment Method <span class="text-danger">*</span></label>
                                    <select class="form-control-modern" id="newPaymentMethod" required>
                                        <option value="cash">Cash</option>
                                        <option value="gcash">GCash</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Payment Status <span class="text-danger">*</span></label>
                                    <select class="form-control-modern" id="newPaymentStatus" required>
                                        <option value="pending">Pending</option>
                                        <option value="paid">Paid</option>
                                        <option value="refunded">Refunded</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group-modern">
                            <label class="form-label-modern">Special Requests</label>
                            <textarea class="form-control-modern" id="newSpecialRequests" rows="2" placeholder="Any special requirements..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer modal-footer-modern">
                    <button type="button" class="btn-modern btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn-modern btn-primary" onclick="createBookingByAdmin()">Create Booking</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ── View Booking Modal ───────────────────────────────── -->
    <div class="modal fade" id="viewBookingModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modal-content-modern">
                <div class="modal-header modal-header-modern">
                    <h5 class="modal-title">
                        <i class="bi bi-eye me-2"></i>Booking Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body modal-body-modern" id="viewBookingBody">
                    <!-- Populated by viewBooking() JS -->
                    <div class="text-center py-4" id="viewBookingLoader">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2" style="color:var(--text-secondary);">Loading booking details…</p>
                    </div>
                </div>
                <div class="modal-footer modal-footer-modern">
                    <button type="button" class="btn-modern btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Data arrays from PHP
        const activitiesList    = <?php echo json_encode($activitiesList); ?>;
        const accommodationsList = <?php echo json_encode($accommodationsList); ?>;
        const bundlesList = <?php echo json_encode($bundlesList); ?>;

        // ── Move modals to <body> on load to escape backdrop-filter stacking contexts ──
        document.addEventListener('DOMContentLoaded', function () {
            ['newBookingModal', 'editBookingModal', 'viewBookingModal'].forEach(function(id) {
                const el = document.getElementById(id);
                if (el && el.parentElement !== document.body) {
                    document.body.appendChild(el);
                }
            });
        });

        // Load components
        fetch('components/sidebar.html')
            .then(response => response.text())
            .then(html => {
                document.getElementById('sidebar-container').innerHTML = html;
            });

        fetch('components/navbar.html')
            .then(response => response.text())
            .then(html => {
                document.getElementById('navbar-container').innerHTML = html;
            });

        // Approve booking
        async function approveBooking(bookingId) {
            if (!confirm('Are you sure you want to approve this booking?')) {
                return;
            }

            try {
                const response = await fetch('../php/update_booking_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        booking_id: bookingId,
                        status: 'approved',
                        notes: 'Booking approved by admin'
                    })
                });

                const result = await response.json();

                if (result.success) {
                    // Reload page to show updated data
                    location.reload();
                } else {
                    showNotification(result.message || 'Failed to approve booking', 'danger');
                }
            } catch (error) {
                console.error('Error approving booking:', error);
                showNotification('Failed to approve booking. Please try again.', 'danger');
            }
        }

        // Reject booking
        async function rejectBooking(bookingId) {
            const reason = prompt('Please enter reason for rejection:');
            if (!reason) {
                return;
            }

            try {
                const response = await fetch('../php/update_booking_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        booking_id: bookingId,
                        status: 'rejected',
                        notes: reason
                    })
                });

                const result = await response.json();

                if (result.success) {
                    // Reload page to show updated data
                    location.reload();
                } else {
                    showNotification(result.message || 'Failed to reject booking', 'danger');
                }
            } catch (error) {
                console.error('Error rejecting booking:', error);
                showNotification('Failed to reject booking. Please try again.', 'danger');
            }
        }

        // ── View Booking Details (Issue #2 fix) ─────────────────────────
        let viewBookingModalObj = null;

        async function viewBooking(bookingId) {
            // Show modal with loader
            if (!viewBookingModalObj) {
                viewBookingModalObj = new bootstrap.Modal(document.getElementById('viewBookingModal'));
            }
            document.getElementById('viewBookingBody').innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2" style="color:var(--text-secondary);">Loading booking details…</p>
                </div>`;
            viewBookingModalObj.show();

            try {
                const response = await fetch(`../php/get_booking_details.php?booking_id=${bookingId}`);
                const result   = await response.json();

                if (!result.success) {
                    document.getElementById('viewBookingBody').innerHTML =
                        `<p class="text-danger"><i class="bi bi-x-circle me-1"></i>${result.message || 'Failed to load booking details'}</p>`;
                    return;
                }

                const b = result.booking;

                // Build type badge
                const typeBadgeClass = b.booking_type === 'activity' ? 'primary'
                    : (b.booking_type === 'bundle' ? 'success' : 'warning');
                const typeBadge = `<span class="badge bg-${typeBadgeClass} me-1" style="font-size:.75rem;">${b.booking_type.charAt(0).toUpperCase() + b.booking_type.slice(1)}</span>`;

                // Build status badge (reuse existing CSS classes)
                const statusBadge = `<span class="status-badge status-${escHtml(b.status_name)}">${escHtml(b.status_name.replace('_', ' '))}</span>`;

                // Format currency
                const amount = parseFloat(b.total_amount || 0).toLocaleString('en-PH', { style: 'currency', currency: 'PHP' });

                // Dates
                const checkIn  = b.check_in_date  ? (b.check_in_date  + (b.check_in_time  ? ' ' + b.check_in_time  : '')) : '—';
                const checkOut = b.check_out_date ? (b.check_out_date + (b.check_out_time ? ' ' + b.check_out_time : '')) : '—';

                // Receipt
                const receiptHtml = b.receipt_file_path
                    ? `<a href="../${escHtml(b.receipt_file_path)}" target="_blank" class="btn-modern btn-secondary" style="font-size:.8rem;padding:.35rem .75rem;">
                           <i class="bi bi-file-earmark-image me-1"></i>View Receipt
                       </a>`
                    : '<span style="color:var(--text-muted);font-size:.875rem;">No receipt uploaded</span>';

                // Change log
                let logsHtml = '<p style="color:var(--text-muted);font-size:.8rem;">No history yet.</p>';
                if (b.logs && b.logs.length) {
                    logsHtml = b.logs.map(log => `
                        <div style="border-left:2px solid var(--border-color);padding:.5rem .75rem;margin-bottom:.5rem;">
                            <div style="font-size:.8rem;color:var(--text-secondary);">
                                <strong style="color:var(--text-primary);">${escHtml(log.changed_by_name || log.changed_by)}</strong>
                                changed status to <strong style="color:var(--accent-primary);">${escHtml(log.new_status_name || '—')}</strong>
                            </div>
                            ${log.notes ? `<div style="font-size:.75rem;color:var(--text-muted);margin-top:.2rem;">${escHtml(log.notes)}</div>` : ''}
                            <div style="font-size:.7rem;color:var(--text-muted);margin-top:.2rem;">${escHtml(log.created_at || '')}</div>
                        </div>`).join('');
                }

                document.getElementById('viewBookingBody').innerHTML = `
                    <!-- Reference + type -->
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                        <div>
                            <span style="font-size:.75rem;color:var(--text-muted);">REFERENCE</span><br>
                            <strong style="font-size:1.1rem;color:var(--accent-primary);letter-spacing:.05em;">${escHtml(b.booking_reference)}</strong>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            ${typeBadge}
                            ${statusBadge}
                        </div>
                    </div>

                    <hr style="border-color:var(--border-color);margin:.75rem 0;">

                    <!-- Two-column grid -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <p class="mb-1" style="font-size:.75rem;color:var(--text-muted);">CUSTOMER</p>
                            <p class="mb-0" style="color:var(--text-primary);font-weight:600;">${escHtml(b.full_name)}</p>
                            <p class="mb-0" style="font-size:.85rem;color:var(--text-secondary);">${escHtml(b.email)}</p>
                            <p class="mb-0" style="font-size:.85rem;color:var(--text-secondary);">${escHtml(b.phone_number || '—')}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1" style="font-size:.75rem;color:var(--text-muted);">ITEM BOOKED</p>
                            <p class="mb-0" style="color:var(--text-primary);font-weight:600;">${escHtml(b.item_name || '—')}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1" style="font-size:.75rem;color:var(--text-muted);">CHECK-IN</p>
                            <p class="mb-0" style="color:var(--text-primary);">${escHtml(checkIn)}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1" style="font-size:.75rem;color:var(--text-muted);">CHECK-OUT</p>
                            <p class="mb-0" style="color:var(--text-primary);">${escHtml(checkOut)}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1" style="font-size:.75rem;color:var(--text-muted);">GUESTS</p>
                            <p class="mb-0" style="color:var(--text-primary);">${escHtml(String(b.number_of_guests))}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1" style="font-size:.75rem;color:var(--text-muted);">TOTAL AMOUNT</p>
                            <p class="mb-0" style="color:var(--accent-success);font-weight:700;">${amount}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1" style="font-size:.75rem;color:var(--text-muted);">PAYMENT</p>
                            <p class="mb-0" style="color:var(--text-primary);">${escHtml(b.payment_method ? b.payment_method.toUpperCase() : '—')} / ${escHtml(b.payment_status || '—')}</p>
                        </div>
                        ${b.special_requests ? `
                        <div class="col-12">
                            <p class="mb-1" style="font-size:.75rem;color:var(--text-muted);">SPECIAL REQUESTS</p>
                            <p class="mb-0" style="color:var(--text-secondary);font-size:.875rem;">${escHtml(b.special_requests)}</p>
                        </div>` : ''}
                        <div class="col-12">
                            <p class="mb-1" style="font-size:.75rem;color:var(--text-muted);">RECEIPT</p>
                            ${receiptHtml}
                        </div>
                    </div>

                    <hr style="border-color:var(--border-color);margin:1rem 0;">

                    <!-- Change history -->
                    <p style="font-size:.75rem;color:var(--text-muted);font-weight:600;letter-spacing:.05em;">CHANGE HISTORY</p>
                    ${logsHtml}
                `;

            } catch (error) {
                console.error('Error loading booking details:', error);
                document.getElementById('viewBookingBody').innerHTML =
                    '<p class="text-danger"><i class="bi bi-x-circle me-1"></i>Connection error. Please try again.</p>';
            }
        }

        // ── Edit Booking (Issue #3 fix) ──────────────────────────────────
        let editBookingModalObj = null;
        let currentEditBookingId = null;

        async function editBooking(bookingId) {
            currentEditBookingId = bookingId;

            if (!editBookingModalObj) {
                editBookingModalObj = new bootstrap.Modal(document.getElementById('editBookingModal'));
            }

            try {
                const response = await fetch(`../php/get_booking_details.php?booking_id=${bookingId}`);
                const result   = await response.json();

                if (!result.success) {
                    showNotification(result.message || 'Could not load booking data', 'danger');
                    return;
                }

                const b = result.booking;

                // Populate simple shared fields
                document.getElementById('editCustomerName').value   = b.full_name      || '';
                document.getElementById('editDate').value           = b.check_in_date  || '';
                document.getElementById('editTime').value           = b.check_in_time  || '';
                document.getElementById('editPaymentMethod').value  = b.payment_method || 'cash';

                // Populate activity/accommodation/bundle from real DB data
                const actSel = document.getElementById('editActivity');
                actSel.innerHTML = '<option value="">-- Select --</option>';

                // Use the appropriate list based on booking type
                let list = [];
                if (b.booking_type === 'activity')      list = activitiesList;
                else if (b.booking_type === 'accommodation') list = accommodationsList;
                else if (b.booking_type === 'bundle')   list = bundlesList;

                list.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = item.name;
                    // Pre-select the current item
                    const currentId = b.activity_id || b.accommodation_id || b.bundle_id;
                    if (item.id == currentId) opt.selected = true;
                    actSel.appendChild(opt);
                });

                editBookingModalObj.show();

            } catch (err) {
                console.error(err);
                showNotification('Failed to load booking for editing.', 'danger');
            }
        }

        // ── Save Booking Changes (Issue #4 fix) ──────────────────────────
        async function saveBookingChanges() {
            if (!currentEditBookingId) return;

            const payload = {
                booking_id:     currentEditBookingId,
                full_name:      document.getElementById('editCustomerName').value.trim(),
                check_in_date:  document.getElementById('editDate').value,
                check_in_time:  document.getElementById('editTime').value || null,
                payment_method: document.getElementById('editPaymentMethod').value,
                item_id:        document.getElementById('editActivity').value || null,
                special_requests: document.getElementById('editSpecialRequests').value,
            };

            if (!payload.full_name || !payload.check_in_date) {
                showNotification('Customer name and date are required.', 'danger');
                return;
            }

            try {
                const res    = await fetch('../php/update_booking.php', {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body:    JSON.stringify(payload)
                });
                const result = await res.json();

                if (result.success) {
                    showNotification('Booking updated successfully!', 'success');
                    if (editBookingModalObj) editBookingModalObj.hide();
                    setTimeout(() => location.reload(), 1200);
                } else {
                    showNotification(result.message || 'Failed to update booking.', 'danger');
                }
            } catch (err) {
                console.error(err);
                showNotification('Connection error. Please try again.', 'danger');
            }
        }

        // Show notification
        function showNotification(message, type) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                min-width: 300px;
                background: ${type === 'success' ? 'rgba(16, 185, 129, 0.9)' : 'rgba(239, 68, 68, 0.9)'};
                color: white;
                border: none;
                border-radius: 0.5rem;
                padding: 1rem;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            `;
            notification.innerHTML = `
                <i class="bi bi-${type === 'success' ? 'check-circle' : 'x-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(notification);
            
            // Auto-remove after 3 seconds
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const statusFilter = document.getElementById('statusFilter');
            const paymentFilter = document.getElementById('paymentFilter');
            const dateFilter = document.getElementById('dateFilter');
            
            function applyFilters() {
                const searchTerm = searchInput.value.toLowerCase();
                const statusValue = statusFilter.value;
                const paymentValue = paymentFilter.value;
                const dateValue = dateFilter.value;
                
                const rows = document.querySelectorAll('#bookingsTableBody tr');
                
                rows.forEach(row => {
                    const bookingId = row.dataset.bookingId;
                    const booking = bookingsData[bookingId];
                    
                    let showRow = true;
                    
                    // Search filter
                    if (searchTerm && !booking.customer.toLowerCase().includes(searchTerm)) {
                        showRow = false;
                    }
                    
                    // Status filter
                    if (statusValue && booking.status !== statusValue) {
                        showRow = false;
                    }
                    
                    // Payment filter
                    if (paymentValue && booking.payment !== paymentValue) {
                        showRow = false;
                    }
                    
                    // Date filter
                    if (dateValue && !booking.date.includes(dateValue)) {
                        showRow = false;
                    }
                    
                    row.style.display = showRow ? '' : 'none';
                });
            }
            
            // Add event listeners
            searchInput.addEventListener('input', applyFilters);
            statusFilter.addEventListener('change', applyFilters);
            paymentFilter.addEventListener('change', applyFilters);
            dateFilter.addEventListener('change', applyFilters);
        });

        let newBookingModalObj = null;

        function openNewBookingModal() {
            console.log('%c[DEBUG] openNewBookingModal() called', 'color: cyan; font-weight: bold;');

            // Step 1: Check if bootstrap is available
            if (typeof bootstrap === 'undefined') {
                console.error('[DEBUG] FAIL: bootstrap is not defined — Bootstrap JS not loaded yet!');
                return;
            }
            console.log('[DEBUG] Step 1 OK — bootstrap is defined');

            // Step 2: Find the modal element
            const modalEl = document.getElementById('newBookingModal');
            if (!modalEl) {
                console.error('[DEBUG] FAIL: #newBookingModal element not found in DOM!');
                return;
            }
            console.log('[DEBUG] Step 2 OK — #newBookingModal element found:', modalEl);

            // Step 3: Create or reuse modal object
            try {
                if (!newBookingModalObj) {
                    newBookingModalObj = new bootstrap.Modal(modalEl);
                    console.log('[DEBUG] Step 3 OK — new bootstrap.Modal created');
                } else {
                    console.log('[DEBUG] Step 3 OK — reusing existing modal object');
                }
            } catch (e) {
                console.error('[DEBUG] FAIL at Step 3 — bootstrap.Modal() threw an error:', e);
                return;
            }

            // Step 4: Reset form fields
            const formEl = document.getElementById('newBookingForm');
            if (!formEl) { console.error('[DEBUG] FAIL: #newBookingForm not found!'); return; }
            formEl.reset();
            console.log('[DEBUG] Step 4 OK — form reset');

            // Step 5: Reset item select
            const itemEl = document.getElementById('newItemId');
            if (!itemEl) { console.error('[DEBUG] FAIL: #newItemId not found!'); return; }
            itemEl.innerHTML = '<option value="">-- Select Booking Type First --</option>';
            itemEl.disabled = true;
            console.log('[DEBUG] Step 5 OK — item select reset');

            // Step 6: Hide conditional rows
            const checkoutRow = document.getElementById('newCheckoutDateRow');
            const timeRow = document.getElementById('newTimeFieldsRow');
            if (!checkoutRow) { console.error('[DEBUG] FAIL: #newCheckoutDateRow not found!'); return; }
            if (!timeRow)     { console.error('[DEBUG] FAIL: #newTimeFieldsRow not found!'); return; }
            checkoutRow.style.display = 'none';
            timeRow.style.display = 'none';
            document.getElementById('newItemCapacityHint').textContent = '';
            console.log('[DEBUG] Step 6 OK — rows hidden');

            // Step 7: Set date defaults
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('newCheckInDate').min = today;
            document.getElementById('newCheckInDate').value = today;
            document.getElementById('newCheckOutDate').min = today;
            console.log('[DEBUG] Step 7 OK — dates set to today:', today);

            // Step 8: Show modal
            try {
                newBookingModalObj.show();
                console.log('[DEBUG] Step 8 OK — modal.show() called successfully!');
            } catch (e) {
                console.error('[DEBUG] FAIL at Step 8 — modal.show() threw an error:', e);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const newCheckInDateEl = document.getElementById('newCheckInDate');
            if (newCheckInDateEl) {
                newCheckInDateEl.addEventListener('change', function() {
                    document.getElementById('newCheckOutDate').min = this.value;
                    if (document.getElementById('newCheckOutDate').value < this.value) {
                        document.getElementById('newCheckOutDate').value = this.value;
                    }
                });
            }
        });

        function onBookingTypeChange() {
            const type = document.getElementById('newBookingType').value;
            const itemSelect = document.getElementById('newItemId');
            const checkoutRow = document.getElementById('newCheckoutDateRow');
            const timeFieldsRow = document.getElementById('newTimeFieldsRow');
            
            itemSelect.innerHTML = '<option value="">-- Select Item --</option>';
            document.getElementById('newItemCapacityHint').textContent = '';
            
            if (!type) {
                itemSelect.disabled = true;
                checkoutRow.style.display = 'none';
                timeFieldsRow.style.display = 'none';
                return;
            }
            
            itemSelect.disabled = false;
            let list = [];
            if (type === 'activity') {
                list = activitiesList;
                checkoutRow.style.display = 'none';
                timeFieldsRow.style.display = 'flex';
                document.getElementById('newCheckInTime').required = true;
                document.getElementById('newCheckOutTime').required = true;
                document.getElementById('newCheckOutDate').required = false;
            } else if (type === 'accommodation') {
                list = accommodationsList;
                checkoutRow.style.display = 'flex';
                timeFieldsRow.style.display = 'none';
                document.getElementById('newCheckInTime').required = false;
                document.getElementById('newCheckOutTime').required = false;
                document.getElementById('newCheckOutDate').required = true;
            } else if (type === 'bundle') {
                list = bundlesList;
                checkoutRow.style.display = 'flex';
                timeFieldsRow.style.display = 'none';
                document.getElementById('newCheckInTime').required = false;
                document.getElementById('newCheckOutTime').required = false;
                document.getElementById('newCheckOutDate').required = true;
            }
            
            list.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.id;
                opt.textContent = item.name + (item.price_per_hour ? ` (₱${item.price_per_hour}/hr)` : (item.price_per_night ? ` (₱${item.price_per_night}/night)` : ` (₱${item.price})`));
                opt.dataset.capacity = item.capacity;
                itemSelect.appendChild(opt);
            });
        }

        function onItemChange() {
            const itemSelect = document.getElementById('newItemId');
            const selectedOpt = itemSelect.options[itemSelect.selectedIndex];
            const capacityHint = document.getElementById('newItemCapacityHint');
            const guestsInput = document.getElementById('newNumberOfGuests');
            
            if (selectedOpt && selectedOpt.dataset.capacity) {
                const cap = selectedOpt.dataset.capacity;
                capacityHint.textContent = `Max capacity: ${cap} guests`;
                guestsInput.max = cap;
                guestsInput.value = Math.min(guestsInput.value, cap);
            } else {
                capacityHint.textContent = '';
                guestsInput.removeAttribute('max');
            }
        }

        async function createBookingByAdmin() {
            const userId = document.getElementById('newCustomerId').value;
            const bookingType = document.getElementById('newBookingType').value;
            const itemId = document.getElementById('newItemId').value;
            const checkInDate = document.getElementById('newCheckInDate').value;
            const checkOutDate = document.getElementById('newCheckOutDate').value;
            const checkInTime = document.getElementById('newCheckInTime').value;
            const checkOutTime = document.getElementById('newCheckOutTime').value;
            const guests = document.getElementById('newNumberOfGuests').value;
            const statusId = document.getElementById('newStatusId').value;
            const paymentMethod = document.getElementById('newPaymentMethod').value;
            const paymentStatus = document.getElementById('newPaymentStatus').value;
            const specialRequests = document.getElementById('newSpecialRequests').value;
            
            // Validation
            if (!userId || !bookingType || !itemId || !checkInDate || !guests || !statusId || !paymentMethod || !paymentStatus) {
                alert('Please fill in all required fields marked with *');
                return;
            }
            
            if (bookingType === 'activity' && (!checkInTime || !checkOutTime)) {
                alert('Please specify Time-In and Time-Out for activity bookings.');
                return;
            }
            
            if ((bookingType === 'accommodation' || bookingType === 'bundle') && !checkOutDate) {
                alert('Please specify Check-out Date.');
                return;
            }
            
            const payload = {
                user_id: userId,
                booking_type: bookingType,
                item_id: itemId,
                check_in_date: checkInDate,
                check_out_date: checkOutDate || null,
                check_in_time: checkInTime || null,
                check_out_time: checkOutTime || null,
                number_of_guests: guests,
                status_id: statusId,
                payment_method: paymentMethod,
                payment_status: paymentStatus,
                special_requests: specialRequests
            };
            
            try {
                const res = await fetch('../php/admin_create_booking.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                
                const result = await res.json();
                if (result.success) {
                    showNotification(result.message || 'Booking created successfully!', 'success');
                    if (newBookingModalObj) {
                        newBookingModalObj.hide();
                    }
                    setTimeout(() => location.reload(), 1500);
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (err) {
                console.error(err);
                alert('Failed to send request to server.');
            }
        }

        // Pagination
        document.addEventListener('click', function(e) {
            if (e.target.closest('.page-btn') && !e.target.closest('.page-btn').innerHTML.includes('i')) {
                document.querySelectorAll('.page-btn').forEach(btn => btn.classList.remove('active'));
                e.target.closest('.page-btn').classList.add('active');
            }
        });

        // ── escHtml utility (used by viewBooking template literals) ──
        function escHtml(s) {
            if (s == null) return '';
            return String(s)
                .replace(/&/g,  '&amp;')
                .replace(/</g,  '&lt;')
                .replace(/>/g,  '&gt;')
                .replace(/"/g,  '&quot;')
                .replace(/'/g,  '&#39;');
        }

    </script>

    <!-- Bootstrap JS already loaded above -->
</body>
</html>
