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

// Fetch real activities from database
$query = "
    SELECT 
        a.id,
        a.name,
        a.slug,
        a.description,
        a.price_per_hour,
        a.price_type,
        a.min_duration_hours,
        a.max_duration_hours,
        a.capacity,
        a.image_url,
        a.is_active,
        a.created_at,
        COUNT(CASE WHEN bs.name IN ('pending_review', 'approved') THEN b.id END) as total_bookings
    FROM activities a
    LEFT JOIN bookings b ON a.id = b.activity_id
    LEFT JOIN booking_status bs ON b.status_id = bs.id
    GROUP BY a.id
    ORDER BY a.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

$activities = [];
while ($row = $result->fetch_assoc()) {
    $activities[] = $row;
}



// Fetch real accommodations/amenities from database
$accQuery = "
    SELECT 
        a.id,
        a.name,
        a.type,
        a.tour_type,
        a.description,
        a.base_capacity,
        a.capacity,
        a.extra_person_fee,
        a.price_per_night,
        a.price_per_day,
        a.amenities,
        a.image_url,
        a.is_active,
        a.created_at,
        COUNT(b.id) as total_bookings
    FROM accommodations a
    LEFT JOIN bookings b ON a.id = b.accommodation_id
    GROUP BY a.id
    ORDER BY a.type ASC, a.created_at DESC
";
$accStmt = $conn->prepare($accQuery);
$accStmt->execute();
$accResult = $accStmt->get_result();

$accommodations = [];
while ($row = $accResult->fetch_assoc()) {
    $accommodations[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activities Management - YP4K Resort</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/resort-theme.css">
    <link rel="stylesheet" href="../css/dashboard-resort.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="components/sidebar.css">
    <link rel="stylesheet" href="components/navbar.css">
    
    <link rel="stylesheet" href="../css/admin-theme.css">
    <style>
        .tab-content, .tab-pane, .activities-grid {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
        }
        .activity-card {
            background: #0b1310 !important; /* very dark grey/black, no green tint */
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            border-radius: 18px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .nav-pills .nav-link {
            background: #08120E !important;
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        .nav-pills .nav-link:hover {
            color: var(--text-primary);
            border-color: var(--accent-primary);
        }
        .nav-pills .nav-link.active {
            background: linear-gradient(135deg, var(--accent-primary), #1d4ed8);
            color: #ffffff;
            border-color: var(--accent-primary);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }
        .activity-card {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .activity-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .activity-actions {
            margin-top: auto;
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .activity-actions .btn-modern {
            flex: 1 1 auto;
            min-width: 80px;
            padding: 0.45rem 0.6rem;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.3rem;
        }
    </style>
    <script src="components/admin-components.js" defer></script>
</head>
<body>
    <div id="sidebar-container"></div>
    <div id="navbar-container"></div>

    <div id="mainContent" class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title">Activities & Amenities Management</h1>
                <p class="text-muted">Manage resort activities, overnight rooms, cottages, and facilities</p>
            </div>
            <div class="page-actions d-flex gap-2 flex-wrap">
                <button class="btn-modern btn-primary" onclick="openAddActivityModal()">
                    <i class="bi bi-plus-circle"></i>
                    Add Activity
                </button>
                <button class="btn-modern" style="background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: none; box-shadow: 0 4px 15px rgba(16,185,129,0.3);" onclick="openAddAccommodationModal()">
                    <i class="bi bi-plus-circle"></i>
                    Add Amenity / Room
                </button>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-pills mb-4 gap-2" id="managementTabs" role="tablist" style="padding-bottom: 1rem;">
            <li class="nav-item" role="presentation">
                <button class="nav-link active d-flex align-items-center gap-2" id="activities-tab" data-bs-toggle="pill" data-bs-target="#activities-section" type="button" role="tab" style="border-radius: 0.5rem; padding: 0.6rem 1.25rem; font-weight: 600;">
                    <i class="bi bi-activity"></i> Activities (<?php echo count($activities); ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link d-flex align-items-center gap-2" id="accommodations-tab" data-bs-toggle="pill" data-bs-target="#accommodations-section" type="button" role="tab" style="border-radius: 0.5rem; padding: 0.6rem 1.25rem; font-weight: 600;">
                    <i class="bi bi-house-door"></i> Amenities & Accommodations (<?php echo count($accommodations); ?>)
                </button>
            </li>
        </ul>

        <div class="tab-content" id="managementTabsContent">
            <!-- ACTIVITIES TAB -->
            <div class="tab-pane fade show active" id="activities-section" role="tabpanel" aria-labelledby="activities-tab">
                <div class="activities-grid">
                    <?php if (count($activities) > 0): ?>
                        <?php foreach ($activities as $activity): ?>
                            <?php
                                $statusClass = $activity['is_active'] ? 'status-active' : 'status-inactive';
                                $statusText = $activity['is_active'] ? 'Active' : 'Inactive';
                                $icon = 'bi-activity';
                                $price = number_format($activity['price_per_hour'], 2);
                                $priceTypeLabels = [
                                    'per_head'  => 'Per Head',
                                    'per_hour'  => 'Per Hour',
                                    'flat_rate' => 'Flat Rate',
                                ];
                                $priceUnit = $priceTypeLabels[$activity['price_type'] ?? 'flat_rate'] ?? 'Per Hour';
                            ?>
                            <div class="activity-card">
                                <div class="activity-image position-relative">
                                    <?php if ($activity['image_url']): ?>
                                        <?php
                                            $activityImgSrc = $activity['image_url'];
                                            if (!preg_match('#^(https?:)?//#i', $activityImgSrc) && strpos($activityImgSrc, '/') !== 0 && strpos($activityImgSrc, '../') !== 0) {
                                                $activityImgSrc = '../' . ltrim($activityImgSrc, '/');
                                            }
                                        ?>
                                        <img src="<?php echo htmlspecialchars($activityImgSrc); ?>" alt="<?php echo htmlspecialchars($activity['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <i class="bi <?php echo $icon; ?>"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-header">
                                        <div>
                                            <h3 class="activity-title"><?php echo htmlspecialchars($activity['name']); ?></h3>
                                        </div>
                                        <span class="activity-status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                    </div>
                                    <div class="activity-stats">
                                        <div class="stat-item">
                                            <div class="stat-value">&#8369;<?php echo $price; ?></div>
                                            <div class="stat-label"><?php echo htmlspecialchars($priceUnit); ?></div>
                                        </div>
                                        <div class="stat-item">
                                            <div class="stat-value"><i class="bi bi-people me-1"></i><?php echo $activity['capacity']; ?></div>
                                            <div class="stat-label">Capacity</div>
                                        </div>
                                        <div class="stat-item">
                                            <div class="stat-value"><?php echo $activity['total_bookings']; ?></div>
                                            <div class="stat-label">Bookings</div>
                                        </div>
                                    </div>
                                    <div class="activity-actions">
                                        <button class="btn-modern btn-primary" onclick="editActivity(<?php echo $activity['id']; ?>)">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <button class="btn-modern <?php echo $activity['is_active'] ? 'btn-secondary' : 'btn-primary'; ?>" style="<?php echo $activity['is_active'] ? 'background:#d97706;border-color:#d97706;color:#fff;' : 'background:#10b981;border-color:#10b981;color:#fff;'; ?>" onclick="toggleActivityStatus(<?php echo $activity['id']; ?>, <?php echo $activity['is_active'] ? 0 : 1; ?>)">
                                            <i class="bi <?php echo $activity['is_active'] ? 'bi-slash-circle' : 'bi-check-circle'; ?>"></i>
                                            <?php echo $activity['is_active'] ? 'Disable' : 'Enable'; ?>
                                        </button>
                                        <button class="btn-modern btn-secondary" style="background:#ef4444;border-color:#ef4444;color:#fff;" onclick="deleteActivity(<?php echo $activity['id']; ?>)">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                            <i class="bi bi-inbox" style="font-size: 3rem; color: var(--text-muted);"></i>
                            <p style="color: var(--text-secondary); margin-top: 1rem;">No activities found in the database.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ACCOMMODATIONS & AMENITIES TAB -->
            <div class="tab-pane fade" id="accommodations-section" role="tabpanel" aria-labelledby="accommodations-tab">
                <div class="activities-grid">
                    <?php if (count($accommodations) > 0): ?>
                        <?php foreach ($accommodations as $acc): ?>
                            <?php
                                $statusClass = $acc['is_active'] ? 'status-active' : 'status-inactive';
                                $statusText = $acc['is_active'] ? 'Active' : 'Inactive';

                                $typeVal = strtolower($acc['type'] ?? 'room');
                                $isCottage = ($typeVal === 'cottage');
                                $icon = $isCottage ? 'bi-shop' : 'bi-house-door';

                                $price = number_format($acc['price_per_night'], 2);
                                $baseCap = !empty($acc['base_capacity']) ? $acc['base_capacity'] : 1;
                                $maxCap = !empty($acc['capacity']) ? $acc['capacity'] : $baseCap;
                                $extraFee = floatval($acc['extra_person_fee'] ?? 0);
                            ?>
                            <div class="activity-card">
                                <div class="activity-image position-relative">
                                    <?php if ($acc['image_url']): ?>
                                        <img src="<?php echo htmlspecialchars($acc['image_url']); ?>" alt="<?php echo htmlspecialchars($acc['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <i class="bi <?php echo $icon; ?>"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-header">
                                        <div>
                                            <h3 class="activity-title"><?php echo htmlspecialchars($acc['name']); ?></h3>
                                            <div class="d-flex align-items-center flex-wrap gap-1 mt-1">
                                                <span class="badge" style="font-size:0.7rem; background:rgba(59,130,246,0.15); color:#60a5fa; border:1px solid rgba(59,130,246,0.3);">
                                                    <i class="bi <?php echo $icon; ?> me-1"></i><?php echo ucfirst($typeVal); ?>
                                                </span>
                                                <?php
                                                    $tourVal = strtolower($acc['tour_type'] ?? '');
                                                    if (strpos($tourVal, 'overnight') !== false):
                                                ?>
                                                    <span class="badge" style="font-size:0.7rem; background:rgba(139,92,246,0.15); color:#c084fc; border:1px solid rgba(139,92,246,0.3);">
                                                        <i class="bi bi-moon-stars me-1"></i>Overnight
                                                    </span>
                                                <?php endif; ?>
                                                <?php if (strpos($tourVal, 'day') !== false): ?>
                                                    <span class="badge" style="font-size:0.7rem; background:rgba(245,158,11,0.15); color:#fbbf24; border:1px solid rgba(245,158,11,0.3);">
                                                        <i class="bi bi-sun me-1"></i>Day Tour
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <span class="activity-status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                    </div>
                                    <div class="activity-stats" style="flex-wrap: wrap;">
                                        <?php if (strpos($tourVal, 'overnight') !== false): ?>
                                        <div class="stat-item" style="min-width: 90px;">
                                            <div class="stat-value">&#8369;<?php echo number_format($acc['price_per_night'], 2); ?></div>
                                            <div class="stat-label">Per Night</div>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (strpos($tourVal, 'day') !== false): ?>
                                        <div class="stat-item" style="min-width: 90px;">
                                            <div class="stat-value">&#8369;<?php echo number_format($acc['price_per_day'], 2); ?></div>
                                            <div class="stat-label">Per Day</div>
                                        </div>
                                        <?php endif; ?>
                                        <div class="stat-item" style="min-width: 90px;">
                                            <div class="stat-value"><i class="bi bi-people me-1"></i><?php echo ($baseCap == $maxCap) ? $maxCap : ($baseCap . '-' . $maxCap); ?></div>
                                            <div class="stat-label">Base/Max Guests</div>
                                        </div>
                                        <div class="stat-item" style="min-width: 90px;">
                                            <div class="stat-value">&#8369;<?php echo number_format($extraFee, 2); ?></div>
                                            <div class="stat-label">Extra Person</div>
                                        </div>
                                        <div class="stat-item" style="min-width: 90px;">
                                            <div class="stat-value"><?php echo $acc['total_bookings']; ?></div>
                                            <div class="stat-label">Bookings</div>
                                        </div>
                                    </div>
                                    <div class="activity-actions">
                                        <button class="btn-modern btn-primary" onclick="editAccommodation(<?php echo $acc['id']; ?>)">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <button class="btn-modern <?php echo $acc['is_active'] ? 'btn-secondary' : 'btn-primary'; ?>" style="<?php echo $acc['is_active'] ? 'background:#d97706;border-color:#d97706;color:#fff;' : 'background:#10b981;border-color:#10b981;color:#fff;'; ?>" onclick="toggleAccommodationStatus(<?php echo $acc['id']; ?>, <?php echo $acc['is_active'] ? 0 : 1; ?>)">
                                            <i class="bi <?php echo $acc['is_active'] ? 'bi-slash-circle' : 'bi-check-circle'; ?>"></i>
                                            <?php echo $acc['is_active'] ? 'Disable' : 'Enable'; ?>
                                        </button>
                                        <button class="btn-modern btn-secondary" style="background:#ef4444;border-color:#ef4444;color:#fff;" onclick="deleteAccommodation(<?php echo $acc['id']; ?>)">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                            <i class="bi bi-house-door" style="font-size: 3rem; color: var(--text-muted);"></i>
                            <p style="color: var(--text-secondary); margin-top: 1rem;">No amenities or accommodations found in the database.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Activity Modal -->
    <div class="modal fade" id="activityModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modal-content-modern">
                <div class="modal-header modal-header-modern">
                    <h5 class="modal-title" id="activityModalTitle">Add Activity</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body modal-body-modern">
                    <form id="activityForm" enctype="multipart/form-data">
                        <input type="hidden" id="activityId" name="activity_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Activity Name</label>
                                    <input type="text" class="form-control-modern" id="activityName" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Price (₱)</label>
                                    <input type="number" step="0.01" class="form-control-modern" id="activityPrice" name="price_per_hour" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group-modern">
                            <label class="form-label-modern">Pricing Type</label>
                            <select class="form-control-modern" id="activityPriceType" name="price_type">
                                <option value="flat_rate">Flat Rate</option>
                                <option value="per_head">Per Head</option>
                                <option value="per_hour">Per Hour</option>
                            </select>
                        </div>
                        <div class="form-group-modern">
                            <label class="form-label-modern">Description</label>
                            <textarea class="form-control-modern" id="activityDescription" name="description" rows="3"></textarea>
                        </div>
                        <div class="form-group-modern">
                            <label class="form-label-modern">Status</label>
                            <select class="form-control-modern" id="activityStatus" name="is_active">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="form-group-modern">
                            <label class="form-label-modern">Activity Image</label>
                            <input type="file" class="form-control-modern" id="activityImage" name="image" accept="image/*">
                            <small style="color:#5C6B63; font-size:0.78rem; margin-top:0.3rem; display:block;">Leave empty to keep existing image (when editing)</small>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Min Duration (hours)</label>
                                    <input type="number" step="0.5" class="form-control-modern" id="minDuration" name="min_duration_hours" value="1.0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Max Duration (hours)</label>
                                    <input type="number" step="0.5" class="form-control-modern" id="maxDuration" name="max_duration_hours" value="4.0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Capacity (per slot)</label>
                                    <input type="number" class="form-control-modern" id="activityCapacity" name="capacity" value="10">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Daily Booking Limit</label>
                                    <input type="number" class="form-control-modern" id="activityDailyLimit" name="daily_booking_limit" value="10">
                                </div>
                            </div>
                        </div>
                        <!-- Time Slot Fields -->
                        <div id="activityTimeSlotWrapper" style="margin-top: 15px;">
                            <h5>Manage Time Slots</h5>
                            <div id="activityTimeSlotContainer">
                                <div class="time-slot-group" style="display: flex; gap: 10px; margin-bottom: 10px; align-items: center;">
                                    <input type="time" name="start_time[]" class="form-control-modern">
                                    <input type="time" name="end_time[]" class="form-control-modern">
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="addTimeSlotRow('activityTimeSlotContainer')">+ Add Time Slot</button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer modal-footer-modern">
                    <button type="button" class="btn-modern btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn-modern btn-primary" onclick="saveActivity()">Save Activity</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Accommodation & Amenity Modal -->
    <div class="modal fade" id="accommodationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modal-content-modern">
                <div class="modal-header modal-header-modern">
                    <h5 class="modal-title" id="accommodationModalTitle">Add Amenity / Room</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body modal-body-modern">
                    <form id="accommodationForm">
                        <input type="hidden" id="accommodationId" name="accommodation_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Amenity / Room Name</label>
                                    <input type="text" class="form-control-modern" id="accName" name="name" required placeholder="e.g., Deluxe Overnight Villa">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Category</label>
                                    <select class="form-control-modern" id="accType" name="type" required>
                                        <option value="room">Room</option>
                                        <option value="cottage">Cottage</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Stay / Tour Option <small style="color: var(--text-muted); font-size: 0.75rem;">(Check one or both)</small></label>
                                    <div class="d-flex align-items-center gap-4 mt-2" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 0.55rem 0.9rem; min-height: 48px;">
                                        <div class="form-check mb-0">
                                            <input class="form-check-input" type="checkbox" name="tour_types[]" id="accTourOvernight" value="overnight" style="cursor: pointer;">
                                            <label class="form-check-label text-white" for="accTourOvernight" style="cursor: pointer; user-select: none;">
                                                <i class="bi bi-moon-stars me-1 text-info"></i> Overnight
                                            </label>
                                        </div>
                                        <div class="form-check mb-0">
                                            <input class="form-check-input" type="checkbox" name="tour_types[]" id="accTourDay" value="day_tour" style="cursor: pointer;" onchange="toggleDayTourSlots()">
                                            <label class="form-check-label text-white" for="accTourDay" style="cursor: pointer; user-select: none;">
                                                <i class="bi bi-sun me-1 text-warning"></i> Day Tour
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Overnight Check-In Time</label>
                                    <input type="time" class="form-control-modern" id="accCheckInTime" name="check_in_time" value="14:00">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Overnight Check-Out Time</label>
                                    <input type="time" class="form-control-modern" id="accCheckOutTime" name="check_out_time" value="12:00">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Price per Night (₱)</label>
                                    <input type="number" step="0.01" class="form-control-modern" id="accPriceNight" name="price_per_night" required value="0.00">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Price per Day (₱)</label>
                                    <input type="number" step="0.01" class="form-control-modern" id="accPriceDay" name="price_per_day" required value="0.00">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Base Capacity (Guests)</label>
                                    <input type="number" min="1" class="form-control-modern" id="accBaseCapacity" name="base_capacity" required value="2">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Max Capacity (Guests)</label>
                                    <input type="number" min="1" class="form-control-modern" id="accCapacity" name="capacity" required value="4">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Daily Limit</label>
                                    <input type="number" min="1" class="form-control-modern" id="accDailyLimit" name="daily_booking_limit" required value="1">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Extra Person (₱)</label>
                                    <input type="number" step="0.01" min="0" class="form-control-modern" id="accExtraPersonFee" name="extra_person_fee" required value="0.00">
                                </div>
                            </div>
                        </div>
                        <div class="form-group-modern">
                            <label class="form-label-modern">Description</label>
                            <textarea class="form-control-modern" id="accDescription" name="description" rows="3" placeholder="Describe the features, view, and furnishings..."></textarea>
                        </div>
                        <div class="form-group-modern">
                            <label class="form-label-modern">Included Amenities & Features (comma-separated)</label>
                            <input type="text" class="form-control-modern" id="accAmenities" name="amenities" placeholder="e.g., Air Conditioning, Free Wi-Fi, Private Bath, King Bed, Ocean View">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Status</label>
                                    <select class="form-control-modern" id="accStatus" name="is_active">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Image Upload</label>
                                    <input type="file" class="form-control-modern" id="accImage" name="image" accept="image/*">
                                    <small style="color:#5C6B63; font-size:0.78rem; margin-top:0.3rem; display:block;">Leave empty to keep existing image when editing</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Time Slot Fields -->
                        <div id="timeSlotWrapper" style="margin-top: 15px; display: none;">
                            <h5>Manage Time Slots</h5>
                            <div id="accTimeSlotContainer">
                                <div class="time-slot-group" style="display: flex; gap: 10px; margin-bottom: 10px; align-items: center;">
                                    <input type="time" name="start_time[]" class="form-control-modern">
                                    <input type="time" name="end_time[]" class="form-control-modern">
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="addTimeSlotRow('accTimeSlotContainer')">+ Add Time Slot</button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer modal-footer-modern">
                    <button type="button" class="btn-modern btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn-modern btn-primary" onclick="saveAccommodation()">Save Amenity / Room</button>
                </div>
            </div>
        </div>
    </div>

    <script>

        // Modal instance
        let activityModal;

        function addTimeSlotRow(containerId) {
            const container = document.getElementById(containerId);
            const row = document.createElement('div');
            row.className = 'time-slot-group';
            row.style = 'display: flex; gap: 10px; margin-bottom: 10px; align-items: center;';
            row.innerHTML = `
                <input type="time" name="start_time[]" class="form-control-modern">
                <input type="time" name="end_time[]" class="form-control-modern">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.parentElement.remove()">X</button>
            `;
            container.appendChild(row);
        }

        function resetTimeSlotContainer(containerId) {
            document.getElementById(containerId).innerHTML = `
                <div class="time-slot-group" style="display: flex; gap: 10px; margin-bottom: 10px; align-items: center;">
                    <input type="time" name="start_time[]" class="form-control-modern">
                    <input type="time" name="end_time[]" class="form-control-modern">
                </div>
            `;
        }

        function populateTimeSlotContainer(containerId, timeSlots) {
            const container = document.getElementById(containerId);
            container.innerHTML = '';
            if (timeSlots && timeSlots.length > 0) {
                timeSlots.forEach((ts, idx) => {
                    const row = document.createElement('div');
                    row.className = 'time-slot-group';
                    row.style = 'display: flex; gap: 10px; margin-bottom: 10px; align-items: center;';
                    row.innerHTML = `
                        <input type="time" name="start_time[]" class="form-control-modern" value="${ts.start_time}">
                        <input type="time" name="end_time[]" class="form-control-modern" value="${ts.end_time}">
                        ${idx > 0 ? '<button type="button" class="btn btn-sm btn-outline-danger" onclick="this.parentElement.remove()">X</button>' : ''}
                    `;
                    container.appendChild(row);
                });
            } else {
                resetTimeSlotContainer(containerId);
            }
        }

        // Open Add Activity Modal
        function openAddActivityModal() {
            document.getElementById('activityModalTitle').textContent = 'Add Activity';
            document.getElementById('activityForm').reset();
            document.getElementById('activityId').value = '';
            document.getElementById('activityStatus').value = '1';
            document.getElementById('activityDailyLimit').value = '10';
            resetTimeSlotContainer('activityTimeSlotContainer');
            
            if (!activityModal) {
                activityModal = new bootstrap.Modal(document.getElementById('activityModal'));
            }
            activityModal.show();
        }

        // Edit Activity
        async function editActivity(activityId) {
            if (!activityId) {
                alert('Activity ID is missing. Please try again.');
                return;
            }
            try {
                const params = new URLSearchParams({
                    action: 'get',
                    activity_id: String(activityId)
                });
                const response = await fetch(`../php/add_edit_activity.php?${params.toString()}`);
                const result = await response.json();

                if (result.success) {
                    const activity = result.activity;
                    
                    document.getElementById('activityModalTitle').textContent = 'Edit Activity';
                    document.getElementById('activityId').value = activity.id;
                    document.getElementById('activityName').value = activity.name;
                    document.getElementById('activityDescription').value = activity.description || '';
                    document.getElementById('activityPrice').value = activity.price_per_hour;
                    document.getElementById('activityPriceType').value = activity.price_type || 'flat_rate';
                    document.getElementById('activityStatus').value = activity.is_active;
                    document.getElementById('minDuration').value = activity.min_duration_hours;
                    document.getElementById('maxDuration').value = activity.max_duration_hours;
                    document.getElementById('activityCapacity').value = activity.capacity;
                    document.getElementById('activityDailyLimit').value = activity.daily_booking_limit || 10;
                    populateTimeSlotContainer('activityTimeSlotContainer', activity.time_slots);
                    
                    if (!activityModal) {
                        activityModal = new bootstrap.Modal(document.getElementById('activityModal'));
                    }
                    activityModal.show();
                } else {
                    alert(result.message || 'Failed to load activity details');
                }
            } catch (error) {
                console.error('Error loading activity:', error);
                alert('Failed to load activity details. Please try again.');
            }
        }

        // Save Activity (Add/Edit)
        async function saveActivity() {
            const form = document.getElementById('activityForm');
            const formData = new FormData(form);
            
            const activityId = document.getElementById('activityId').value;
            formData.append('action', activityId ? 'edit' : 'add');
            
            // Validate required fields
            const name = document.getElementById('activityName').value.trim();
            const price = document.getElementById('activityPrice').value;
            
            if (!name) {
                alert('Please enter an activity name');
                return;
            }
            if (!price || parseFloat(price) <= 0) {
                alert('Please enter a valid price');
                return;
            }

            try {
                const response = await fetch('../php/add_edit_activity.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    activityModal.hide();
                    sessionStorage.setItem('activeManagementTab', 'activities-tab');
                    location.reload();
                } else {
                    alert(result.message || 'Failed to save activity');
                }
            } catch (error) {
                console.error('Error saving activity:', error);
                alert('Failed to save activity. Please try again.');
            }
        }

        // Delete Activity
        async function deleteActivity(activityId) {
            if (!confirm('Are you sure you want to delete this activity? This action cannot be undone.')) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('activity_id', activityId);

                const response = await fetch('../php/add_edit_activity.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    sessionStorage.setItem('activeManagementTab', 'activities-tab');
                    location.reload();
                } else {
                    alert(result.message || 'Failed to delete activity');
                }
            } catch (error) {
                console.error('Error deleting activity:', error);
                alert('Failed to delete activity. Please try again.');
            }
        }

        // Toggle Activity Status (Disable/Enable)
        async function toggleActivityStatus(activityId, newStatus) {
            try {
                const formData = new FormData();
                formData.append('action', 'toggle');
                formData.append('activity_id', activityId);
                formData.append('is_active', newStatus);

                const response = await fetch('../php/add_edit_activity.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    sessionStorage.setItem('activeManagementTab', 'activities-tab');
                    location.reload();
                } else {
                    alert(result.message || 'Failed to update activity status');
                }
            } catch (error) {
                console.error('Error toggling status:', error);
                alert('Failed to update status. Please try again.');
            }
        }

        // ── ACCOMMODATIONS & AMENITIES MANAGEMENT ────────────────────────
        let accommodationModal;

        function toggleDayTourSlots() {
            const dayBox = document.getElementById('accTourDay');
            const timeSlotWrapper = document.getElementById('timeSlotWrapper');
            if (dayBox && timeSlotWrapper) {
                timeSlotWrapper.style.display = dayBox.checked ? 'block' : 'none';
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            const dayBox = document.getElementById('accTourDay');
            if (dayBox) {
                dayBox.addEventListener('change', toggleDayTourSlots);
            }
        });

        function openAddAccommodationModal() {
            document.getElementById('accommodationModalTitle').textContent = 'Add Amenity / Room';
            document.getElementById('accommodationForm').reset();
            document.getElementById('accommodationId').value = '';
            document.getElementById('accStatus').value = '1';
            document.getElementById('accType').value = 'room';
            document.getElementById('accTourOvernight').checked = true;
            document.getElementById('accTourDay').checked = false;
            document.getElementById('accBaseCapacity').value = '2';
            document.getElementById('accCapacity').value = '4';
            document.getElementById('accDailyLimit').value = '1';
            document.getElementById('accExtraPersonFee').value = '0.00';
            document.getElementById('accPriceNight').value = '0.00';
            document.getElementById('accPriceDay').value = '0.00';
            document.getElementById('accCheckInTime').value = '14:00';
            document.getElementById('accCheckOutTime').value = '12:00';
            resetTimeSlotContainer('accTimeSlotContainer');
            toggleDayTourSlots();
            
            if (!accommodationModal) {
                accommodationModal = new bootstrap.Modal(document.getElementById('accommodationModal'));
            }
            accommodationModal.show();
        }

        async function editAccommodation(accId) {
            try {
                const response = await fetch(`../php/add_edit_accommodation.php?action=get&accommodation_id=${accId}`);
                const result = await response.json();

                if (result.success) {
                    const acc = result.accommodation;
                    
                    document.getElementById('accommodationModalTitle').textContent = 'Edit Amenity / Room';
                    document.getElementById('accommodationId').value = acc.id;
                    document.getElementById('accName').value = acc.name;
                    
                    // Dropdown: Room or Cottage
                    const typeVal = String(acc.type || 'room').toLowerCase();
                    document.getElementById('accType').value = (typeVal === 'cottage') ? 'cottage' : 'room';
                    
                    // Checkboxes: Overnight and/or Day Tour
                    const tourVal = String(acc.tour_type || '').toLowerCase();
                    if (tourVal) {
                        document.getElementById('accTourOvernight').checked = tourVal.includes('overnight');
                        document.getElementById('accTourDay').checked = tourVal.includes('day');
                    } else {
                        // fallback
                        document.getElementById('accTourOvernight').checked = (typeVal !== 'cottage' || parseFloat(acc.price_per_night) > 0);
                        document.getElementById('accTourDay').checked = (typeVal === 'cottage' || parseFloat(acc.price_per_day) > 0);
                    }
                    
                    document.getElementById('accPriceNight').value = acc.price_per_night;
                    document.getElementById('accPriceDay').value = acc.price_per_day;
                    document.getElementById('accCheckInTime').value = acc.check_in_time ? acc.check_in_time.substring(0, 5) : '14:00';
                    document.getElementById('accCheckOutTime').value = acc.check_out_time ? acc.check_out_time.substring(0, 5) : '12:00';
                    document.getElementById('accBaseCapacity').value = acc.base_capacity !== undefined ? acc.base_capacity : 1;
                    document.getElementById('accCapacity').value = acc.capacity;
                    document.getElementById('accDailyLimit').value = acc.daily_booking_limit || 1;
                    document.getElementById('accExtraPersonFee').value = acc.extra_person_fee !== undefined ? acc.extra_person_fee : '0.00';
                    document.getElementById('accDescription').value = acc.description || '';
                    document.getElementById('accAmenities').value = acc.amenities || '';
                    document.getElementById('accStatus').value = acc.is_active;
                    
                    populateTimeSlotContainer('accTimeSlotContainer', acc.time_slots);
                    toggleDayTourSlots();

                    if (!accommodationModal) {
                        accommodationModal = new bootstrap.Modal(document.getElementById('accommodationModal'));
                    }
                    accommodationModal.show();
                } else {
                    alert(result.message || 'Failed to load amenity/room details');
                }
            } catch (error) {
                console.error('Error loading accommodation:', error);
                alert('Failed to load amenity details. Please try again.');
            }
        }

        async function saveAccommodation() {
            const form = document.getElementById('accommodationForm');
            const overnightChecked = document.getElementById('accTourOvernight').checked;
            const dayChecked = document.getElementById('accTourDay').checked;
            
            if (!overnightChecked && !dayChecked) {
                alert('Please select at least one Stay / Tour option (Overnight and/or Day Tour).');
                return;
            }

            const name = document.getElementById('accName').value.trim();
            if (!name) {
                alert('Please enter an amenity or room name');
                return;
            }

            const baseCap = parseInt(document.getElementById('accBaseCapacity').value) || 1;
            const maxCap = parseInt(document.getElementById('accCapacity').value) || 1;
            if (baseCap > maxCap) {
                alert('Base capacity cannot exceed maximum capacity.');
                return;
            }

            const formData = new FormData(form);
            const accId = document.getElementById('accommodationId').value;
            formData.append('action', accId ? 'edit' : 'add');

            try {
                const response = await fetch('../php/add_edit_accommodation.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                if (result.success) {
                    accommodationModal.hide();
                    sessionStorage.setItem('activeManagementTab', 'accommodations-tab');
                    location.reload();
                } else {
                    alert(result.message || 'Failed to save amenity / room');
                }
            } catch (error) {
                console.error('Error saving accommodation:', error);
                alert('Failed to save amenity. Please try again.');
            }
        }

        async function deleteAccommodation(accId) {
            if (!confirm('Are you sure you want to delete this amenity / room? This action cannot be undone.')) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('accommodation_id', accId);

                const response = await fetch('../php/add_edit_accommodation.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    sessionStorage.setItem('activeManagementTab', 'accommodations-tab');
                    location.reload();
                } else {
                    alert(result.message || 'Failed to delete amenity / room');
                }
            } catch (error) {
                console.error('Error deleting accommodation:', error);
                alert('Failed to delete amenity. Please try again.');
            }
        }

        async function toggleAccommodationStatus(accId, newStatus) {
            try {
                const formData = new FormData();
                formData.append('action', 'toggle');
                formData.append('accommodation_id', accId);
                formData.append('is_active', newStatus);

                const response = await fetch('../php/add_edit_accommodation.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    sessionStorage.setItem('activeManagementTab', 'accommodations-tab');
                    location.reload();
                } else {
                    alert(result.message || 'Failed to update status');
                }
            } catch (error) {
                console.error('Error toggling status:', error);
                alert('Failed to update status. Please try again.');
            }
        }

        // View Analytics (placeholder)
        function viewAnalytics(activityId) {
            alert('Analytics feature coming soon for activity ID: ' + activityId);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const activeTab = sessionStorage.getItem('activeManagementTab');
            if (activeTab) {
                const tabEl = document.getElementById(activeTab);
                if (tabEl) {
                    const bsTab = new bootstrap.Tab(tabEl);
                    bsTab.show();
                }
                sessionStorage.removeItem('activeManagementTab');
            }
        });
    </script>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>



