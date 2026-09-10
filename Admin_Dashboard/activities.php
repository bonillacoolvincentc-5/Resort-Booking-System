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

// #region agent log
try {
    $dbgBreakdown = $conn->query("
        SELECT a.id AS activity_id, a.name,
               COUNT(b.id) AS all_bookings,
               SUM(CASE WHEN bs.name IN ('pending_review','approved') THEN 1 ELSE 0 END) AS active_bookings,
               GROUP_CONCAT(DISTINCT bs.name) AS status_names
        FROM activities a
        LEFT JOIN bookings b ON a.id = b.activity_id
        LEFT JOIN booking_status bs ON b.status_id = bs.id
        GROUP BY a.id
        ORDER BY a.id
    ");
    $dbgRows = [];
    if ($dbgBreakdown) {
        while ($dbgRow = $dbgBreakdown->fetch_assoc()) { $dbgRows[] = $dbgRow; }
    }
    $dbgPayload = json_encode([
        'sessionId' => '1b1b88',
        'runId' => 'post-fix',
        'hypothesisId' => 'A',
        'location' => 'Admin_Dashboard/activities.php:display-count',
        'message' => 'Activity card booking counts vs status breakdown',
        'data' => ['rows' => $dbgRows, 'unfiltered_card_counts' => array_map(function ($a) {
            return ['id' => $a['id'], 'total_bookings' => $a['total_bookings']];
        }, $activities)],
        'timestamp' => round(microtime(true) * 1000)
    ]) . "\n";
    file_put_contents(dirname(__DIR__) . '/debug-1b1b88.log', $dbgPayload, FILE_APPEND);
} catch (Throwable $e) {}
// #endregion

// Fetch real accommodations/amenities from database
$accQuery = "
    SELECT 
        a.id,
        a.name,
        a.type,
        a.description,
        a.capacity,
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
        .nav-pills .nav-link {
            background: var(--bg-card);
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
        <ul class="nav nav-pills mb-4 gap-2" id="managementTabs" role="tablist" style="border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
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
                                    <span class="badge bg-primary position-absolute" style="top: 10px; left: 10px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; background: rgba(15,23,42,0.85) !important; backdrop-filter: blur(4px); border: 1px solid rgba(255,255,255,0.15);">
                                        Activity
                                    </span>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-header">
                                        <div>
                                            <h3 class="activity-title"><?php echo htmlspecialchars($activity['name']); ?></h3>
                                            <p class="activity-description"><?php echo htmlspecialchars($activity['description'] ?: 'No description available.'); ?></p>
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
                                $icon = 'bi-house-door';
                                if ($acc['type'] === 'cottage') $icon = 'bi-shop';
                                elseif ($acc['type'] === 'stay-in') $icon = 'bi-building';
                                
                                $price = number_format($acc['price_per_night'], 2);
                            ?>
                            <div class="activity-card">
                                <div class="activity-image position-relative">
                                    <?php if ($acc['image_url']): ?>
                                        <img src="<?php echo htmlspecialchars($acc['image_url']); ?>" alt="<?php echo htmlspecialchars($acc['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <i class="bi <?php echo $icon; ?>"></i>
                                    <?php endif; ?>
                                    <span class="badge bg-primary position-absolute" style="top: 10px; left: 10px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; background: rgba(16,185,129,0.85) !important; backdrop-filter: blur(4px); border: 1px solid rgba(255,255,255,0.15);">
                                        <?php echo ucfirst($acc['type']); ?>
                                    </span>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-header">
                                        <div>
                                            <h3 class="activity-title"><?php echo htmlspecialchars($acc['name']); ?></h3>
                                            <p class="activity-description"><?php echo htmlspecialchars($acc['description'] ?: 'No description available.'); ?></p>
                                        </div>
                                        <span class="activity-status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                    </div>
                                    <div class="activity-stats">
                                        <div class="stat-item">
                                            <div class="stat-value">&#8369;<?php echo $price; ?></div>
                                            <div class="stat-label">Per Night</div>
                                        </div>
                                        <div class="stat-item">
                                            <div class="stat-value"><i class="bi bi-people me-1"></i><?php echo $acc['capacity']; ?></div>
                                            <div class="stat-label">Max Guests</div>
                                        </div>
                                        <div class="stat-item">
                                            <div class="stat-value"><?php echo $acc['total_bookings']; ?></div>
                                            <div class="stat-label">Bookings</div>
                                        </div>
                                    </div>
                                    <?php if (!empty($acc['amenities'])): ?>
                                        <div style="font-size: 0.78rem; color: var(--text-secondary); margin-bottom: 0.75rem; background: rgba(255,255,255,0.03); padding: 0.5rem 0.75rem; border-radius: 0.35rem;">
                                            <i class="bi bi-stars text-warning me-1"></i> <strong>Features:</strong> <?php echo htmlspecialchars(mb_strimwidth($acc['amenities'], 0, 65, '...')); ?>
                                        </div>
                                    <?php endif; ?>
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
                                    <label class="form-label-modern">Capacity</label>
                                    <input type="number" class="form-control-modern" id="activityCapacity" name="capacity" value="10">
                                </div>
                            </div>
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
                                    <label class="form-label-modern">Type / Category</label>
                                    <select class="form-control-modern" id="accType" name="type" required>
                                        <option value="room">Overnight Room</option>
                                        <option value="cottage">Cottage / Gazebo</option>
                                        <option value="stay-in">Stay-in / Cabana</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Price per Night (₱)</label>
                                    <input type="number" step="0.01" class="form-control-modern" id="accPriceNight" name="price_per_night" required value="0.00">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Price per Day (₱)</label>
                                    <input type="number" step="0.01" class="form-control-modern" id="accPriceDay" name="price_per_day" required value="0.00">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Max Capacity (Guests)</label>
                                    <input type="number" class="form-control-modern" id="accCapacity" name="capacity" required value="4">
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

        // Open Add Activity Modal
        function openAddActivityModal() {
            document.getElementById('activityModalTitle').textContent = 'Add Activity';
            document.getElementById('activityForm').reset();
            document.getElementById('activityId').value = '';
            document.getElementById('activityStatus').value = '1';
            
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

        function openAddAccommodationModal() {
            document.getElementById('accommodationModalTitle').textContent = 'Add Amenity / Room';
            document.getElementById('accommodationForm').reset();
            document.getElementById('accommodationId').value = '';
            document.getElementById('accStatus').value = '1';
            document.getElementById('accType').value = 'room';
            
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
                    document.getElementById('accType').value = acc.type;
                    document.getElementById('accPriceNight').value = acc.price_per_night;
                    document.getElementById('accPriceDay').value = acc.price_per_day;
                    document.getElementById('accCapacity').value = acc.capacity;
                    document.getElementById('accDescription').value = acc.description || '';
                    document.getElementById('accAmenities').value = acc.amenities || '';
                    document.getElementById('accStatus').value = acc.is_active;
                    
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
            const formData = new FormData(form);
            
            const accId = document.getElementById('accommodationId').value;
            formData.append('action', accId ? 'edit' : 'add');
            
            const name = document.getElementById('accName').value.trim();
            if (!name) {
                alert('Please enter an amenity or room name');
                return;
            }

            try {
                const response = await fetch('../php/add_edit_accommodation.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                if (result.success) {
                    accommodationModal.hide();
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
    </script>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>



