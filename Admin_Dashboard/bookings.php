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
            WHEN b.booking_type = 'activity' THEN 'bi-activity'
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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings Management - YP4K Resort</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                            <div class="col-12">
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



    <!-- -- View Booking Modal --------------------------------- -->
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
                        <p class="mt-2" style="color:var(--text-secondary);">Loading booking details�</p>
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

        // -- Move modals to <body> on load to escape backdrop-filter stacking contexts --
        document.addEventListener('DOMContentLoaded', function () {
            ['editBookingModal', 'viewBookingModal'].forEach(function(id) {
                const el = document.getElementById(id);
                if (el && el.parentElement !== document.body) {
                    document.body.appendChild(el);
                }
            });
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

        // -- View Booking Details (Issue #2 fix) -------------------------
        let viewBookingModalObj = null;

        async function viewBooking(bookingId) {
            // Show modal with loader
            if (!viewBookingModalObj) {
                viewBookingModalObj = new bootstrap.Modal(document.getElementById('viewBookingModal'));
            }
            document.getElementById('viewBookingBody').innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2" style="color:var(--text-secondary);">Loading booking details�</p>
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
                const checkIn  = b.check_in_date  ? (b.check_in_date  + (b.check_in_time  ? ' ' + b.check_in_time  : '')) : '�';
                const checkOut = b.check_out_date ? (b.check_out_date + (b.check_out_time ? ' ' + b.check_out_time : '')) : '�';

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
                                changed status to <strong style="color:var(--accent-primary);">${escHtml(log.new_status_name || '�')}</strong>
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
                            <p class="mb-0" style="font-size:.85rem;color:var(--text-secondary);">${escHtml(b.phone_number || '�')}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1" style="font-size:.75rem;color:var(--text-muted);">ITEM BOOKED</p>
                            <p class="mb-0" style="color:var(--text-primary);font-weight:600;">${escHtml(b.item_name || '�')}</p>
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
                            <p class="mb-1" style="font-size:.75rem;color:var(--text-muted);">PAYMENT STATUS</p>
                            <p class="mb-0" style="color:var(--text-primary);">${escHtml((b.payment_status || 'Pending').toUpperCase())}</p>
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

        // -- Edit Booking (Issue #3 fix) ----------------------------------
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

        // -- Save Booking Changes (Issue #4 fix) --------------------------
        async function saveBookingChanges() {
            if (!currentEditBookingId) return;

            const payload = {
                booking_id:     currentEditBookingId,
                full_name:      document.getElementById('editCustomerName').value.trim(),
                check_in_date:  document.getElementById('editDate').value,
                check_in_time:  document.getElementById('editTime').value || null,
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
            const dateFilter = document.getElementById('dateFilter');
            
            function applyFilters() {
                const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
                const statusValue = statusFilter ? statusFilter.value.toLowerCase() : '';
                const dateValue = dateFilter ? dateFilter.value : '';
                
                const rows = document.querySelectorAll('#bookingsTableBody tr');
                
                rows.forEach(row => {
                    if (!row.dataset.bookingId) return;
                    
                    const customerName = (row.querySelector('.customer-name')?.textContent || '').toLowerCase();
                    const statusText = (row.querySelector('.status-badge')?.textContent || '').toLowerCase();
                    const dateText = (row.cells[1]?.textContent || '').toLowerCase();
                    
                    let showRow = true;
                    
                    if (searchTerm && !customerName.includes(searchTerm)) {
                        showRow = false;
                    }
                    
                    if (statusValue && !statusText.includes(statusValue)) {
                        showRow = false;
                    }
                    
                    if (dateValue && !dateText.includes(dateValue.toLowerCase())) {
                        showRow = false;
                    }
                    
                    row.style.display = showRow ? '' : 'none';
                });
            }
            
            // Add event listeners
            if (searchInput) searchInput.addEventListener('input', applyFilters);
            if (statusFilter) statusFilter.addEventListener('change', applyFilters);
            if (dateFilter) dateFilter.addEventListener('change', applyFilters);
        });

        // Pagination
        document.addEventListener('click', function(e) {
            if (e.target.closest('.page-btn') && !e.target.closest('.page-btn').innerHTML.includes('i')) {
                document.querySelectorAll('.page-btn').forEach(btn => btn.classList.remove('active'));
                e.target.closest('.page-btn').classList.add('active');
            }
        });

        // -- escHtml utility (used by viewBooking template literals) --
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



