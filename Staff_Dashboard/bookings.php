<?php
session_start();
if (!isset($_SESSION['is_staff']) || $_SESSION['is_staff'] !== true) {
    header('Location: ../Authentication/staff-login.php');
    exit();
}
require_once '../phpAuthentication/db_config.php';

$adminName = $_SESSION['admin_name'] ?? 'Staff';
$adminEmail = $_SESSION['admin_email'] ?? '';
global $conn;

// Auto-archive past-due approved bookings → Completed (before UI loads)
$conn->query("
    UPDATE bookings b
    INNER JOIN booking_status bs_approved ON b.status_id = bs_approved.id AND bs_approved.name = 'approved'
    INNER JOIN booking_status bs_completed ON bs_completed.name = 'completed'
    SET b.status_id = bs_completed.id,
        b.updated_at = NOW()
    WHERE b.check_in_date < CURDATE()
");

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
    <title>Bookings | Staff Panel - YP4K Resort</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/resort-theme.css">
    <link rel="stylesheet" href="../css/dashboard-resort.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Include Components -->
    <link rel="stylesheet" href="components/sidebar.css">
    <link rel="stylesheet" href="components/navbar.css">
    
    <link rel="stylesheet" href="../css/admin-theme.css">
    <script src="components/staff-components.js" defer></script>
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

                <a href="walkin.php" class="btn-modern btn-primary"
                   id="openWalkInBtn"
                   style="background:linear-gradient(135deg,#6366f1,#4f46e5);border:none;text-decoration:none;display:inline-flex;align-items:center;gap:.4rem;">
                    <i class="bi bi-person-walking"></i>
                    New Walk-In
                </a>
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
                    <label class="filter-label">Date Range</label>
                    <input type="date" class="filter-input" id="dateFilter">
                </div>
            </div>
        </div>

        <!-- Bookings Table -->
        <div class="bookings-card">

            <!-- ── Status Filter Tabs ──────────────────────────────── -->
            <div class="booking-status-tabs" id="bookingStatusTabs">
                <button class="bst-tab bst-pending active" data-filter="pending_review">
                    <span class="bst-dot"></span>
                    Pending Review
                    <span class="bst-count" id="cntPending">0</span>
                </button>
                <button class="bst-tab bst-approved" data-filter="approved">
                    <span class="bst-dot"></span>
                    Approved
                    <span class="bst-count" id="cntApproved">0</span>
                </button>
                <button class="bst-tab bst-rejected" data-filter="rejected">
                    <span class="bst-dot"></span>
                    Rejected
                    <span class="bst-count" id="cntRejected">0</span>
                </button>
                <button class="bst-tab bst-all" data-filter="booking_records">
                    Booking Records
                    <span class="bst-count" id="cntRecords">0</span>
                </button>
            </div>

            <style>
                .booking-status-tabs {
                    display: flex;
                    align-items: center;
                    gap: 0.35rem;
                    padding: 1rem 1.5rem 0;
                    flex-wrap: wrap;
                    border-bottom: 1px solid var(--border);
                    margin-bottom: 0;
                }
                .bst-tab {
                    display: inline-flex;
                    align-items: center;
                    gap: 0.45rem;
                    padding: 0.55rem 1rem;
                    font-family: var(--font, 'Poppins', sans-serif);
                    font-size: 0.82rem;
                    font-weight: 600;
                    color: var(--text-muted);
                    background: transparent;
                    border: 1px solid transparent;
                    border-bottom: none;
                    border-radius: var(--radius-sm, 10px) var(--radius-sm, 10px) 0 0;
                    cursor: pointer;
                    transition: all 0.2s ease;
                    position: relative;
                    bottom: -1px;
                    white-space: nowrap;
                }
                .bst-tab:hover { color: var(--text); background: rgba(255,255,255,0.04); }
                .bst-dot {
                    width: 7px; height: 7px;
                    border-radius: 50%;
                    display: inline-block;
                    flex-shrink: 0;
                }
                .bst-pending .bst-dot  { background: #F7B731; box-shadow: 0 0 6px rgba(247,183,49,0.5); }
                .bst-approved .bst-dot { background: #27C46A; box-shadow: 0 0 6px rgba(39,196,106,0.5); }
                .bst-rejected .bst-dot { background: #FF5A5F; box-shadow: 0 0 6px rgba(255,90,95,0.5); }
                .bst-all .bst-dot      { display: none; }
                .bst-count {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    min-width: 20px; height: 18px;
                    padding: 0 5px;
                    border-radius: 9px;
                    font-size: 0.7rem;
                    font-weight: 700;
                    background: rgba(255,255,255,0.08);
                    color: var(--text-muted);
                    transition: all 0.2s ease;
                }
                .bst-tab.active {
                    color: var(--text);
                    background: rgba(21,34,29,0.9);
                    border-color: var(--border);
                }
                .bst-pending.active  { color: #F7B731; border-top-color: #F7B731; }
                .bst-approved.active { color: #27C46A; border-top-color: #27C46A; }
                .bst-rejected.active { color: #FF5A5F; border-top-color: #FF5A5F; }
                .bst-all.active      { color: var(--accent); border-top-color: var(--accent); }
                .bst-pending.active  .bst-count { background: rgba(247,183,49,0.15);  color: #F7B731; }
                .bst-approved.active .bst-count { background: rgba(39,196,106,0.15);  color: #27C46A; }
                .bst-rejected.active .bst-count { background: rgba(255,90,95,0.15);   color: #FF5A5F; }
                .bst-all.active      .bst-count { background: rgba(157,255,0,0.12);   color: var(--accent); }
                #bookingsTableBody tr.bst-hidden { display: none; }
                #bstEmptyRow { display: none; }
                #bstEmptyRow td {
                    text-align: center;
                    padding: 2.5rem 1rem;
                    color: var(--text-muted);
                }
                #bstEmptyRow .bst-empty-icon {
                    font-size: 2rem;
                    display: block;
                    margin-bottom: 0.5rem;
                    opacity: 0.4;
                }
            </style>

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
                            <tr data-booking-id="<?php echo $booking['id']; ?>" data-status="<?php echo htmlspecialchars($booking['status_name']); ?>">
                                <td class="customer-name"><?php echo htmlspecialchars($booking['full_name']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($booking['check_in_date'])); ?></td>
                                <td><?php echo htmlspecialchars($booking['item_name']); ?></td>
                                <td><span class="status-badge status-<?php echo $booking['status_name']; ?>"><?php echo ucfirst(str_replace('_', ' ', $booking['status_name'])); ?></span></td>
                                <td>
                                    <div class="action-buttons">
                                                                                <button class="btn-action btn-view" onclick="viewBooking(<?php echo $booking['id']; ?>)">
                                            <i class="bi bi-eye"></i>
                                            View
                                        </button>                                    </div>
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
                    <!-- Empty-state: shown by JS when the active tab has 0 rows -->
                    <tr id="bstEmptyRow">
                        <td colspan="5">
                            <i class="bi bi-funnel bst-empty-icon"></i>
                            No bookings found under this status.
                        </td>
                    </tr>
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

    <!-- ═══ Walk-In Reservation Modal ════════════════════════════════════ -->
    <div class="modal fade" id="walkInModal" tabindex="-1" aria-labelledby="walkInModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content modal-content-modern">

                <!-- Header -->
                <div class="modal-header modal-header-modern">
                    <h5 class="modal-title" id="walkInModalLabel">
                        <i class="bi bi-person-walking me-2"></i>New Walk-In Reservation
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Body -->
                <div class="modal-body modal-body-modern">

                    <!-- Alert area -->
                    <div id="walkInAlert" class="mb-3" style="display:none;"></div>

                    <form id="walkInForm" novalidate>

                        <!-- Row 1: Guest Name + Contact -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern" for="wi_guest_name">
                                        <i class="bi bi-person me-1"></i>Guest Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" id="wi_guest_name"
                                           class="form-control-modern"
                                           placeholder="e.g. Juan dela Cruz"
                                           maxlength="255" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern" for="wi_guest_contact">
                                        <i class="bi bi-telephone me-1"></i>Contact Number <span class="text-danger">*</span>
                                    </label>
                                    <input type="tel" id="wi_guest_contact"
                                           class="form-control-modern"
                                           placeholder="09XXXXXXXXX"
                                           maxlength="11"
                                           pattern="^09\d{9}$" required>
                                </div>
                            </div>
                        </div>

                        <!-- Row 2: Booking Category + Item -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-5">
                                <div class="form-group-modern">
                                    <label class="form-label-modern" for="wi_booking_type">
                                        <i class="bi bi-grid me-1"></i>Booking Category <span class="text-danger">*</span>
                                    </label>
                                    <select id="wi_booking_type" class="form-control-modern" required
                                            onchange="wiPopulateItems()">
                                        <option value="">-- Select Category --</option>
                                        <option value="activity">Activity</option>
                                        <option value="accommodation">Room / Cottage</option>
                                        <option value="bundle">Bundle Package</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="form-group-modern">
                                    <label class="form-label-modern" for="wi_item_id">
                                        <i class="bi bi-list-ul me-1"></i>Specific Item <span class="text-danger">*</span>
                                    </label>
                                    <select id="wi_item_id" class="form-control-modern" required>
                                        <option value="">-- Choose category first --</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Row 3: Check-In Date + Time -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <div class="form-group-modern">
                                    <label class="form-label-modern" for="wi_checkin_date">
                                        <i class="bi bi-calendar-event me-1"></i>Check-In Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" id="wi_checkin_date"
                                           class="form-control-modern" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group-modern">
                                    <label class="form-label-modern" for="wi_checkin_time">
                                        <i class="bi bi-clock me-1"></i>Start Time
                                    </label>
                                    <input type="time" id="wi_checkin_time"
                                           class="form-control-modern">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group-modern">
                                    <label class="form-label-modern" for="wi_checkout_time">
                                        <i class="bi bi-clock-history me-1"></i>End Time
                                    </label>
                                    <input type="time" id="wi_checkout_time"
                                           class="form-control-modern">
                                </div>
                            </div>
                        </div>

                        <!-- Row 4: Check-Out Date + Guests -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern" for="wi_checkout_date">
                                        <i class="bi bi-calendar-check me-1"></i>Check-Out Date
                                        <small class="text-muted">(rooms/bundles)</small>
                                    </label>
                                    <input type="date" id="wi_checkout_date"
                                           class="form-control-modern">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern" for="wi_num_guests">
                                        <i class="bi bi-people me-1"></i>Number of Guests
                                    </label>
                                    <input type="number" id="wi_num_guests"
                                           class="form-control-modern"
                                           value="1" min="1" max="100">
                                </div>
                            </div>
                        </div>

                        <!-- Row 5: Payment Method -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">
                                        <i class="bi bi-cash-coin me-1"></i>Payment Method
                                    </label>
                                    <input type="text" class="form-control-modern"
                                           value="Cash" readonly
                                           style="opacity:0.7;cursor:default;">
                                    <input type="hidden" id="wi_payment_method" value="cash">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern" for="wi_special_requests">
                                        <i class="bi bi-chat-left-text me-1"></i>Special Requests
                                    </label>
                                    <textarea id="wi_special_requests"
                                              class="form-control-modern"
                                              rows="2"
                                              placeholder="Any notes for this guest..."></textarea>
                                </div>
                            </div>
                        </div>

                    </form>
                </div><!-- /.modal-body -->

                <!-- Footer -->
                <div class="modal-footer modal-footer-modern">
                    <button type="button" class="btn-modern btn-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn-modern btn-primary" id="walkInSubmitBtn"
                            onclick="submitWalkIn()"
                            style="background:linear-gradient(135deg,#6366f1,#4f46e5);border:none;">
                        <i class="bi bi-check-circle me-1"></i>Confirm Walk-In
                    </button>
                </div>

            </div>
        </div>
    </div>
    <!-- ═══ End Walk-In Modal ════════════════════════════════════════════ -->

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


    <!-- ═══ Reject Booking Modal ═════════════════════════════════════════ -->
    <div id="rejectBookingModal" class="rbm-overlay" role="dialog" aria-modal="true" aria-labelledby="rbmTitle" style="display:none;">
        <div class="rbm-dialog">
            <!-- Header -->
            <div class="rbm-header">
                <div class="rbm-header-icon">
                    <i class="bi bi-x-octagon-fill"></i>
                </div>
                <div>
                    <h5 class="rbm-title" id="rbmTitle">Reject Booking</h5>
                    <p class="rbm-subtitle">This action will notify the guest of the rejection.</p>
                </div>
                <button class="rbm-close" onclick="closeRejectModal()" aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="rbm-body">
                <label class="rbm-label" for="rbmReason">Please enter reason for rejection:</label>
                <textarea
                    id="rbmReason"
                    class="rbm-textarea"
                    rows="4"
                    placeholder="e.g. Booking slot is no longer available for the requested date…"
                    maxlength="1000"
                ></textarea>
                <div id="rbmError" class="rbm-error" style="display:none;">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span id="rbmErrorMsg">Please enter a rejection reason.</span>
                </div>
            </div>

            <!-- Footer -->
            <div class="rbm-footer">
                <button class="rbm-btn rbm-btn-cancel" onclick="closeRejectModal()" id="rbmCancelBtn">
                    Cancel
                </button>
                <button class="rbm-btn rbm-btn-confirm" onclick="confirmRejectBooking()" id="rbmConfirmBtn">
                    <i class="bi bi-x-circle me-1"></i>
                    Confirm Rejection
                </button>
            </div>
        </div>
    </div>
    <!-- ═══ End Reject Booking Modal ════════════════════════════════════ -->

    <style>
        /* ── Reject Booking Modal ─────────────────────────────────────── */
        .rbm-overlay {
            position: fixed;
            inset: 0;
            z-index: 1060;
            background: rgba(0, 0, 0, 0.65);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            animation: rbmFadeIn 0.2s ease;
        }
        @keyframes rbmFadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        .rbm-dialog {
            background: #15221d;
            border: 1px solid rgba(157, 255, 0, 0.18);
            border-radius: 16px;
            width: 100%;
            max-width: 460px;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.55), 0 0 0 1px rgba(255,255,255,0.04);
            animation: rbmSlideUp 0.22s cubic-bezier(0.34, 1.56, 0.64, 1);
            overflow: hidden;
        }
        @keyframes rbmSlideUp {
            from { transform: translateY(24px) scale(0.97); opacity: 0; }
            to   { transform: translateY(0)     scale(1);    opacity: 1; }
        }
        .rbm-header {
            display: flex;
            align-items: flex-start;
            gap: 0.85rem;
            padding: 1.4rem 1.5rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }
        .rbm-header-icon {
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(255, 90, 95, 0.15);
            border: 1px solid rgba(255, 90, 95, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FF5A5F;
            font-size: 1.1rem;
        }
        .rbm-title {
            margin: 0 0 0.1rem;
            font-family: 'Inter', 'Poppins', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            color: #f1f5f9;
            line-height: 1.3;
        }
        .rbm-subtitle {
            margin: 0;
            font-size: 0.78rem;
            color: #64748b;
            line-height: 1.4;
        }
        .rbm-close {
            margin-left: auto;
            flex-shrink: 0;
            background: transparent;
            border: none;
            color: #64748b;
            font-size: 1rem;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 6px;
            line-height: 1;
            transition: color 0.15s, background 0.15s;
        }
        .rbm-close:hover {
            color: #f1f5f9;
            background: rgba(255,255,255,0.07);
        }
        .rbm-body {
            padding: 1.25rem 1.5rem;
        }
        .rbm-label {
            display: block;
            font-family: 'Inter', 'Poppins', sans-serif;
            font-size: 0.82rem;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 0.6rem;
        }
        .rbm-textarea {
            width: 100%;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 10px;
            color: #f1f5f9;
            font-family: 'Inter', 'Poppins', sans-serif;
            font-size: 0.875rem;
            padding: 0.75rem 1rem;
            resize: vertical;
            min-height: 100px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            box-sizing: border-box;
        }
        .rbm-textarea::placeholder { color: #475569; }
        .rbm-textarea:focus {
            border-color: rgba(157, 255, 0, 0.45);
            box-shadow: 0 0 0 3px rgba(157, 255, 0, 0.08);
        }
        .rbm-textarea.rbm-invalid {
            border-color: rgba(255, 90, 95, 0.55);
            box-shadow: 0 0 0 3px rgba(255, 90, 95, 0.08);
        }
        .rbm-error {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            margin-top: 0.6rem;
            font-size: 0.8rem;
            color: #f87171;
        }
        .rbm-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.65rem;
            padding: 1rem 1.5rem 1.4rem;
            border-top: 1px solid rgba(255,255,255,0.07);
        }
        .rbm-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            padding: 0.55rem 1.25rem;
            font-family: 'Inter', 'Poppins', sans-serif;
            font-size: 0.85rem;
            font-weight: 600;
            border-radius: 9px;
            cursor: pointer;
            transition: all 0.18s ease;
            border: 1px solid transparent;
            line-height: 1;
        }
        .rbm-btn:disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }
        .rbm-btn-cancel {
            background: transparent;
            border-color: rgba(255,255,255,0.18);
            color: #94a3b8;
        }
        .rbm-btn-cancel:hover:not(:disabled) {
            background: rgba(255,255,255,0.06);
            border-color: rgba(255,255,255,0.28);
            color: #f1f5f9;
        }
        .rbm-btn-confirm {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border-color: transparent;
            color: #fff;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.35);
        }
        .rbm-btn-confirm:hover:not(:disabled) {
            background: linear-gradient(135deg, #f87171, #ef4444);
            box-shadow: 0 6px 18px rgba(239, 68, 68, 0.45);
            transform: translateY(-1px);
        }
        .rbm-btn-confirm:active:not(:disabled) {
            transform: translateY(0);
        }
    </style>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Data arrays from PHP
        const activitiesList    = <?php echo json_encode($activitiesList); ?>;
        const accommodationsList = <?php echo json_encode($accommodationsList); ?>;
        const bundlesList = <?php echo json_encode($bundlesList); ?>;

        // -- Move modals to <body> on load to escape backdrop-filter stacking contexts --
        document.addEventListener('DOMContentLoaded', function () {
            ['editBookingModal', 'viewBookingModal', 'walkInModal', 'rejectBookingModal'].forEach(function(id) {
                const el = document.getElementById(id);
                if (el && el.parentElement !== document.body) {
                    document.body.appendChild(el);
                }
            });
            // Set today as minimum date for walk-in check-in
            document.getElementById('wi_checkin_date').min = new Date().toISOString().split('T')[0];
        });

        // ── Walk-In Modal Logic ───────────────────────────────────────────────
        let walkInModalObj = null;

        function openWalkInModal() {
            if (!walkInModalObj) {
                walkInModalObj = new bootstrap.Modal(document.getElementById('walkInModal'));
            }
            // Reset form
            document.getElementById('walkInForm').reset();
            wiSetAlert('', '');
            document.getElementById('wi_item_id').innerHTML = '<option value="">-- Choose category first --</option>';
            document.getElementById('walkInSubmitBtn').disabled = false;
            document.getElementById('walkInSubmitBtn').innerHTML = '<i class="bi bi-check-circle me-1"></i>Confirm Walk-In';
            walkInModalObj.show();
        }

        // Populate item <select> based on chosen category
        function wiPopulateItems() {
            const type = document.getElementById('wi_booking_type').value;
            const sel  = document.getElementById('wi_item_id');
            sel.innerHTML = '<option value="">-- Select item --</option>';

            let list = [];
            if      (type === 'activity')      list = activitiesList;
            else if (type === 'accommodation') list = accommodationsList;
            else if (type === 'bundle')        list = bundlesList;

            list.forEach(item => {
                const opt = document.createElement('option');
                opt.value       = item.id;
                const priceHint = item.price_per_hour
                    ? ` — ₱${parseFloat(item.price_per_hour).toLocaleString()}/hr`
                    : (item.price_per_night
                        ? ` — ₱${parseFloat(item.price_per_night).toLocaleString()}/night`
                        : (item.price ? ` — ₱${parseFloat(item.price).toLocaleString()}` : ''));
                opt.textContent = item.name + priceHint;
                sel.appendChild(opt);
            });
        }

        // Show/hide alert inside the walk-in modal
        function wiSetAlert(msg, type) {
            const el = document.getElementById('walkInAlert');
            if (!msg) { el.style.display = 'none'; return; }
            el.style.display = 'block';
            const icon = type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill';
            const bg   = type === 'success'
                ? 'rgba(16,185,129,0.12)' : 'rgba(239,68,68,0.12)';
            const border = type === 'success'
                ? 'rgba(16,185,129,0.35)' : 'rgba(239,68,68,0.35)';
            const color  = type === 'success' ? '#34d399' : '#f87171';
            el.innerHTML = `
                <div style="background:${bg};border:1px solid ${border};border-radius:.6rem;
                            padding:.75rem 1rem;color:${color};font-size:.875rem;display:flex;align-items:flex-start;gap:.5rem;">
                    <i class="bi bi-${icon}" style="margin-top:.1rem;"></i>
                    <span>${escHtml(msg)}</span>
                </div>`;
        }

        // Submit walk-in booking via fetch
        async function submitWalkIn() {
            const btn = document.getElementById('walkInSubmitBtn');

            const payload = {
                guest_name:      document.getElementById('wi_guest_name').value.trim(),
                guest_contact:   document.getElementById('wi_guest_contact').value.trim(),
                booking_type:    document.getElementById('wi_booking_type').value,
                item_id:         parseInt(document.getElementById('wi_item_id').value) || 0,
                check_in_date:   document.getElementById('wi_checkin_date').value,
                check_in_time:   document.getElementById('wi_checkin_time').value || null,
                check_out_date:  document.getElementById('wi_checkout_date').value || null,
                check_out_time:  document.getElementById('wi_checkout_time').value || null,
                num_guests:      parseInt(document.getElementById('wi_num_guests').value) || 1,
                payment_method:  document.getElementById('wi_payment_method').value,
                special_requests:document.getElementById('wi_special_requests').value.trim() || null,
            };

            // Client-side quick checks
            if (!payload.guest_name)    { wiSetAlert('Guest name is required.', 'danger'); return; }
            if (!/^09\d{9}$/.test(payload.guest_contact)) {
                wiSetAlert('Contact must be a valid PH mobile number (09XXXXXXXXX).', 'danger'); return;
            }
            if (!payload.booking_type)  { wiSetAlert('Please select a booking category.', 'danger'); return; }
            if (!payload.item_id)       { wiSetAlert('Please select a specific item.', 'danger'); return; }
            if (!payload.check_in_date) { wiSetAlert('Check-in date is required.', 'danger'); return; }

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Processing…';
            wiSetAlert('', '');

            try {
                const res    = await fetch('../php/process_walkin_booking.php', {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body:    JSON.stringify(payload),
                });
                const result = await res.json();

                if (result.success) {
                    wiSetAlert(
                        `✅ Walk-in booked! Reference: <strong>${escHtml(result.reference)}</strong> — ${escHtml(result.item_name)} | ₱${parseFloat(result.total).toLocaleString()}`,
                        'success'
                    );
                    btn.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>Done!';
                    setTimeout(() => {
                        if (walkInModalObj) walkInModalObj.hide();
                        location.reload();
                    }, 2500);
                } else {
                    wiSetAlert(result.message || 'Something went wrong. Please try again.', 'danger');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Confirm Walk-In';
                }
            } catch (err) {
                console.error('Walk-in submit error:', err);
                wiSetAlert('Connection error. Please check your network and retry.', 'danger');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Confirm Walk-In';
            }
        }
        // ── End Walk-In Modal Logic ───────────────────────────────────────────

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

        // ── Reject Booking Modal Logic ────────────────────────────────────────
        let _rejectBookingId = null;

        function rejectBooking(bookingId) {
            _rejectBookingId = bookingId;
            // Reset state
            const textarea = document.getElementById('rbmReason');
            const errorEl  = document.getElementById('rbmError');
            const confirmBtn = document.getElementById('rbmConfirmBtn');
            const cancelBtn  = document.getElementById('rbmCancelBtn');
            textarea.value = '';
            textarea.classList.remove('rbm-invalid');
            errorEl.style.display = 'none';
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="bi bi-x-circle me-1"></i>Confirm Rejection';
            cancelBtn.disabled = false;
            // Show modal
            const modal = document.getElementById('rejectBookingModal');
            modal.style.display = 'flex';
            setTimeout(() => textarea.focus(), 60);
        }

        function closeRejectModal() {
            const modal = document.getElementById('rejectBookingModal');
            modal.style.display = 'none';
            _rejectBookingId = null;
            document.getElementById('rbmReason').value = '';
            document.getElementById('rbmReason').classList.remove('rbm-invalid');
            document.getElementById('rbmError').style.display = 'none';
        }

        // Close on overlay backdrop click
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('rejectBookingModal').addEventListener('click', function (e) {
                if (e.target === this) closeRejectModal();
            });
            // Close on Escape key
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && document.getElementById('rejectBookingModal').style.display !== 'none') {
                    closeRejectModal();
                }
            });
        });

        async function confirmRejectBooking() {
            const textarea   = document.getElementById('rbmReason');
            const errorEl    = document.getElementById('rbmError');
            const errorMsg   = document.getElementById('rbmErrorMsg');
            const confirmBtn = document.getElementById('rbmConfirmBtn');
            const cancelBtn  = document.getElementById('rbmCancelBtn');
            const reason     = textarea.value.trim();

            // Validate
            if (!reason) {
                textarea.classList.add('rbm-invalid');
                errorMsg.textContent = 'Please enter a rejection reason before confirming.';
                errorEl.style.display = 'flex';
                textarea.focus();
                return;
            }
            textarea.classList.remove('rbm-invalid');
            errorEl.style.display = 'none';

            // Lock UI
            confirmBtn.disabled = true;
            cancelBtn.disabled  = true;
            confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Rejecting…';

            try {
                const response = await fetch('../php/update_booking_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        booking_id: _rejectBookingId,
                        status: 'rejected',
                        notes: reason
                    })
                });

                const result = await response.json();

                if (result.success) {
                    confirmBtn.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>Rejected!';
                    setTimeout(() => {
                        closeRejectModal();
                        location.reload();
                    }, 900);
                } else {
                    errorMsg.textContent = result.message || 'Failed to reject booking. Please try again.';
                    errorEl.style.display = 'flex';
                    confirmBtn.disabled = false;
                    cancelBtn.disabled  = false;
                    confirmBtn.innerHTML = '<i class="bi bi-x-circle me-1"></i>Confirm Rejection';
                }
            } catch (error) {
                console.error('Error rejecting booking:', error);
                errorMsg.textContent = 'Connection error. Check your network and try again.';
                errorEl.style.display = 'flex';
                confirmBtn.disabled = false;
                cancelBtn.disabled  = false;
                confirmBtn.innerHTML = '<i class="bi bi-x-circle me-1"></i>Confirm Rejection';
            }
        }
        // ── End Reject Booking Modal Logic ───────────────────────────────────

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
                const checkIn  = b.check_in_date  ? (b.check_in_date  + (b.check_in_time  ? ' ' + b.check_in_time  : '')) : '';
                const checkOut = b.check_out_date ? (b.check_out_date + (b.check_out_time ? ' ' + b.check_out_time : '')) : '';

                // GCash Payment Proof / Receipt
                const proofPath = b.payment_proof || b.receipt_file_path;
                const receiptHtml = proofPath
                    ? `<div style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.3);border-radius:0.75rem;padding:1rem;margin-top:0.3rem;">
                           <div class="d-flex align-items-center justify-content-between mb-2">
                               <span style="font-size:.75rem;font-weight:700;color:#10b981;text-transform:uppercase;letter-spacing:0.5px;">
                                   <i class="bi bi-check-circle-fill me-1"></i>GCash Payment Proof Attached
                               </span>
                               <a href="../${escHtml(proofPath)}" target="_blank" class="btn-modern btn-secondary" style="font-size:.75rem;padding:.3rem .75rem;">
                                   <i class="bi bi-box-arrow-up-right me-1"></i>Open Full Size
                               </a>
                           </div>
                           <div style="text-align:center;background:#0f172a;border-radius:0.5rem;padding:0.75rem;border:1px solid rgba(51,65,85,0.6);">
                               <a href="../${escHtml(proofPath)}" target="_blank" title="Click to view full image">
                                   <img src="../${escHtml(proofPath)}" alt="GCash Payment Proof" style="max-height:240px;max-width:100%;object-fit:contain;border-radius:0.35rem;cursor:pointer;">
                               </a>
                           </div>
                       </div>`
                    : `<div style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.25);border-radius:0.75rem;padding:0.85rem;color:#f87171;font-size:.85rem;display:flex;align-items:center;gap:0.5rem;">
                           <i class="bi bi-exclamation-triangle-fill"></i>
                           <span>No GCash payment proof image attached for this booking.</span>
                       </div>`;

                // Change log
                let logsHtml = '<p style="color:var(--text-muted);font-size:.8rem;">No history yet.</p>';
                if (b.logs && b.logs.length) {
                    logsHtml = b.logs.map(log => `
                        <div style="border-left:2px solid var(--border-color);padding:.5rem .75rem;margin-bottom:.5rem;">
                            <div style="font-size:.8rem;color:var(--text-secondary);">
                                <strong style="color:var(--text-primary);">${escHtml(log.changed_by_name || log.changed_by)}</strong>
                                changed status to <strong style="color:var(--accent-primary);">${escHtml(log.new_status_name || '')}</strong>
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
                            <p class="mb-0" style="font-size:.85rem;color:var(--text-secondary);">${escHtml(b.phone_number || '')}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1" style="font-size:.75rem;color:var(--text-muted);">ITEM BOOKED</p>
                            <p class="mb-0" style="color:var(--text-primary);font-weight:600;">${escHtml(b.item_name || '')}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1" style="font-size:.75rem;color:var(--text-muted);">CHECK-IN</p>
                            <p class="mb-0" style="color:var(--text-primary);">${escHtml(checkIn)}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1" style="font-size:.75rem;color:var(--text-muted);">CHECK-OUT</p>
                            <p class="mb-0" style="color:var(--text-primary);">${escHtml(checkOut)}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1" style="font-size:.75rem;color:var(--text-muted);">GUESTS</p>
                            <p class="mb-0" style="color:var(--text-primary);">${escHtml(String(b.number_of_guests))}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1" style="font-size:.75rem;color:var(--text-muted);">TOTAL AMOUNT</p>
                            <p class="mb-0" style="color:var(--accent-success);font-weight:700;">${amount}</p>
                        </div>
                        ${b.special_requests ? `
                        <div class="col-12">
                            <p class="mb-1" style="font-size:.75rem;color:var(--text-muted);">SPECIAL REQUESTS</p>
                            <p class="mb-0" style="color:var(--text-secondary);font-size:.875rem;">${escHtml(b.special_requests)}</p>
                        </div>` : ''}
                        <div class="col-12">
                            <p class="mb-1" style="font-size:.75rem;color:var(--text-muted);">GCASH PAYMENT PROOF</p>
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

        // Filter functionality + Status Tab wiring
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput  = document.getElementById('searchInput');
            const dateFilter   = document.getElementById('dateFilter');
            const emptyRow     = document.getElementById('bstEmptyRow');

            // Active tab — default to Pending Review on load
            let activeBstFilter = 'pending_review';

            // Count badges: tally rows by status
            function updateCounts() {
                const allRows = document.querySelectorAll('#bookingsTableBody tr[data-status]');
                const counts  = { pending_review: 0, approved: 0, rejected: 0, completed: 0 };
                allRows.forEach(r => {
                    const s = r.dataset.status;
                    if (s in counts) counts[s]++;
                });
                document.getElementById('cntPending').textContent  = counts.pending_review;
                document.getElementById('cntApproved').textContent = counts.approved;
                document.getElementById('cntRejected').textContent = counts.rejected;
                const cntRecords = document.getElementById('cntRecords');
                if (cntRecords) cntRecords.textContent = counts.completed + counts.rejected;
            }

            // Unified filter: tab + search + status-select + date
            function applyFilters() {
                const searchTerm = searchInput  ? searchInput.value.toLowerCase()  : '';
                const dateValue  = dateFilter   ? dateFilter.value                 : '';

                const rows = document.querySelectorAll('#bookingsTableBody tr[data-status]');
                let visible = 0;

                rows.forEach(row => {
                    const rowStatus    = row.dataset.status || '';
                    const customerName = (row.querySelector('.customer-name')?.textContent || '').toLowerCase();
                    const statusText   = (row.querySelector('.status-badge')?.textContent  || '').toLowerCase();
                    const dateText     = (row.cells[1]?.textContent || '').toLowerCase();

                    const tabOk = (() => {
                        if (activeBstFilter === 'booking_records') {
                            return rowStatus === 'completed' || rowStatus === 'rejected';
                        }
                        return rowStatus === activeBstFilter;
                    })();
                    const searchOk = !searchTerm || customerName.includes(searchTerm);
                    const dateOk   = !dateValue  || dateText.includes(dateValue.toLowerCase());

                    if (tabOk && searchOk && dateOk) {
                        row.classList.remove('bst-hidden');
                        visible++;
                    } else {
                        row.classList.add('bst-hidden');
                    }

                    const hideArchiveActions = activeBstFilter === 'booking_records';
                    row.querySelectorAll('.btn-approve, .btn-reject').forEach(btn => {
                        btn.style.display = hideArchiveActions ? 'none' : '';
                    });
                });

                if (emptyRow) emptyRow.style.display = visible === 0 ? '' : 'none';
            }

            // Tab click handler
            document.querySelectorAll('#bookingStatusTabs .bst-tab').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('#bookingStatusTabs .bst-tab')
                            .forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    activeBstFilter = this.dataset.filter;
                    applyFilters();
                });
            });

            // Existing filter inputs
            if (searchInput)  searchInput.addEventListener('input',  applyFilters);
            if (dateFilter)   dateFilter.addEventListener('change',   applyFilters);

            // Boot: populate counts then apply the default Pending Review tab
            updateCounts();
            applyFilters();
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

        // -- Send Arrival Reminder ------------------------------------------
        async function notifyBooking(id) {
            if (!confirm('Send arrival reminder email to this guest?')) return;
            
            // Show loading state on the button
            const btn = document.querySelector(`tr[data-booking-id="${id}"] .notification-btn`);
            const originalHTML = btn ? btn.innerHTML : '';
            if (btn) {
                btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="width: 1rem; height: 1rem; border-width: 0.15em;"></span>';
                btn.disabled = true;
            }

            try {
                const res = await fetch('../php/send_arrival_reminder.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ booking_id: id })
                });
                
                const result = await res.json();
                
                if (result.success) {
                    showNotification(result.message, 'success');
                } else {
                    showNotification(result.message || 'Failed to send reminder.', 'danger');
                }
            } catch (err) {
                console.error(err);
                showNotification('Connection error. Please try again.', 'danger');
            } finally {
                if (btn) {
                    btn.innerHTML = originalHTML;
                    btn.disabled = false;
                }
            }
        }


    </script>

    <!-- Bootstrap JS already loaded above -->
</body>
</html>




