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
                <button class="btn-modern btn-primary">
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

        // View booking details
        async function viewBooking(bookingId) {
            try {
                const response = await fetch(`../php/get_booking_details.php?booking_id=${bookingId}`);
                const result = await response.json();

                if (result.success) {
                    const booking = result.booking;
                    
                    // Show booking details in a simple alert for now
                    alert(`
Booking Reference: ${booking.booking_reference}
Customer: ${booking.full_name}
Email: ${booking.email}
Phone: ${booking.phone_number}
Item: ${booking.item_name}
Check-in: ${booking.check_in_date} ${booking.check_in_time || ''}
Check-out: ${booking.check_out_date || ''} ${booking.check_out_time || ''}
Guests: ${booking.number_of_guests}
Total Amount: $${booking.total_amount}
Status: ${booking.status_name}
Payment: ${booking.payment_status}
                    `);
                } else {
                    showNotification(result.message || 'Failed to load booking details', 'danger');
                }
            } catch (error) {
                console.error('Error loading booking details:', error);
                showNotification('Failed to load booking details. Please try again.', 'danger');
            }
        }

        // Edit booking (placeholder - not implemented)
        function editBooking(bookingId) {
            showNotification('Edit functionality coming soon', 'warning');
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

        // Pagination
        document.addEventListener('click', function(e) {
            if (e.target.closest('.page-btn') && !e.target.closest('.page-btn').innerHTML.includes('i')) {
                document.querySelectorAll('.page-btn').forEach(btn => btn.classList.remove('active'));
                e.target.closest('.page-btn').classList.add('active');
            }
        });

    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
