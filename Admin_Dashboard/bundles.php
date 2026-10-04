<?php
session_start();

require_once 'config.php';

// Admin-only guard
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../Authentication/admin-login.php');
    exit();
}

admin_include('db_config.php');
$adminName  = $_SESSION['admin_name'];
$adminEmail = $_SESSION['admin_email'];
global $conn;

// -- Ensure tables exist -------------------------------------------
$conn->query("
    CREATE TABLE IF NOT EXISTS `bundles` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `slug` varchar(255) NOT NULL,
        `description` text DEFAULT NULL,
        `price` decimal(10,2) NOT NULL DEFAULT 0.00,
        `capacity` int(11) NOT NULL DEFAULT 1,
        `amenities` text DEFAULT NULL,
        `image_url` varchar(500) DEFAULT NULL,
        `is_active` tinyint(1) DEFAULT 1,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`), UNIQUE KEY `slug` (`slug`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$conn->query("
    CREATE TABLE IF NOT EXISTS `bundle_activities` (
        `id` int(11) NOT NULL AUTO_INCREMENT, `bundle_id` int(11) NOT NULL, `activity_id` int(11) NOT NULL,
        PRIMARY KEY (`id`), KEY `bundle_id` (`bundle_id`), KEY `activity_id` (`activity_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$conn->query("
    CREATE TABLE IF NOT EXISTS `bundle_accommodations` (
        `id` int(11) NOT NULL AUTO_INCREMENT, `bundle_id` int(11) NOT NULL, `accommodation_id` int(11) NOT NULL,
        PRIMARY KEY (`id`), KEY `bundle_id` (`bundle_id`), KEY `accommodation_id` (`accommodation_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// -- Fetch bundles with relation counts ----------------------------
$result = $conn->query("
    SELECT
        b.id, b.name, b.slug, b.description, b.price, b.capacity,
        b.amenities, b.image_url, b.is_active, b.created_at,
        COUNT(DISTINCT ba.activity_id)      AS activity_count,
        COUNT(DISTINCT bacc.accommodation_id) AS accommodation_count
    FROM bundles b
    LEFT JOIN bundle_activities      ba   ON b.id = ba.bundle_id
    LEFT JOIN bundle_accommodations  bacc ON b.id = bacc.bundle_id
    GROUP BY b.id
    ORDER BY b.created_at DESC
");

$bundles = [];
while ($row = $result->fetch_assoc()) $bundles[] = $row;

// -- Fetch activities for modal checkboxes -------------------------
$actResult     = $conn->query("SELECT id, name, price_per_hour FROM activities WHERE is_active = 1 ORDER BY name ASC");
$allActivities = [];
while ($r = $actResult->fetch_assoc()) $allActivities[] = $r;

// -- Fetch accommodations for modal checkboxes ---------------------
$accResult      = $conn->query("SELECT id, name, type, price_per_night FROM accommodations WHERE is_active = 1 ORDER BY type ASC, name ASC");
$allAccommodations = [];
while ($r = $accResult->fetch_assoc()) $allAccommodations[] = $r;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bundles � YP4K Resort Admin</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
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

    <!-- Toast container -->
    <div class="toast-wrap" id="toastWrap"></div>

    <!-- -- MAIN CONTENT ------------------------------------------- -->
    <div id="mainContent" class="main-content">

        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title">Manage Bundles</h1>
                <p class="text-muted">Create and manage resort packages visible to users on the booking page</p>
            </div>
            <div class="page-actions">
                <button class="btn-modern btn-primary" onclick="openAddModal()">
                    <i class="bi bi-plus-circle"></i> Add Bundle
                </button>
            </div>
        </div>

        <!-- Stats row -->
        <div class="stats-row" id="statsRow">
            <?php
            $total    = count($bundles);
            $active   = count(array_filter($bundles, fn($b) => $b['is_active']));
            $inactive = $total - $active;
            ?>
            <div class="stats-mini">
                <div class="stats-mini-icon purple"><i class="bi bi-box-seam"></i></div>
                <div><div class="stats-mini-val"><?= $total ?></div><div class="stats-mini-lbl">Total Bundles</div></div>
            </div>
            <div class="stats-mini">
                <div class="stats-mini-icon green"><i class="bi bi-check-circle"></i></div>
                <div><div class="stats-mini-val"><?= $active ?></div><div class="stats-mini-lbl">Available</div></div>
            </div>
            <div class="stats-mini">
                <div class="stats-mini-icon amber"><i class="bi bi-pause-circle"></i></div>
                <div><div class="stats-mini-val"><?= $inactive ?></div><div class="stats-mini-lbl">Unavailable</div></div>
            </div>
        </div>

        <!-- Bundles grid -->
        <div class="bundles-grid" id="bundlesGrid">
            <?php if ($bundles): ?>
                <?php foreach ($bundles as $b): ?>
                <?php
                    $statusClass = $b['is_active'] ? 'status-active' : 'status-inactive';
                    $statusText  = $b['is_active'] ? 'Available' : 'Unavailable';
                    $price       = number_format($b['price'], 2);
                ?>
                <div class="bundle-card" id="bundle-<?= $b['id'] ?>">
                    <div class="bundle-image">
                        <?php if ($b['image_url']): ?>
                            <img src="../<?= htmlspecialchars($b['image_url']) ?>"
                                 alt="<?= htmlspecialchars($b['name']) ?>"
                                 onerror="this.parentElement.innerHTML='<i class=\'bi bi-box-seam\'></i>'">
                        <?php else: ?>
                            <i class="bi bi-box-seam"></i>
                        <?php endif; ?>
                        <span class="bundle-type-badge">Bundle</span>
                    </div>
                    <div class="bundle-content">
                        <div class="bundle-header">
                            <div>
                                <div class="bundle-title"><?= htmlspecialchars($b['name']) ?></div>
                            </div>
                            <span class="bundle-status <?= $statusClass ?>"><?= $statusText ?></span>
                        </div>
                        <p class="bundle-desc"><?= htmlspecialchars(mb_strimwidth($b['description'] ?? 'No description.', 0, 110, '�')) ?></p>
                        <div class="bundle-stats">
                            <div class="stat-item">
                                <div class="stat-value">&#8369;<?= $price ?></div>
                                <div class="stat-label">Price</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value"><?= $b['capacity'] ?></div>
                                <div class="stat-label">Capacity</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value"><?= (int)$b['activity_count'] ?></div>
                                <div class="stat-label">Activities</div>
                            </div>
                        </div>
                        <div class="bundle-actions">
                            <button class="btn-modern btn-primary" onclick="editBundle(<?= $b['id'] ?>)">
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            <button class="btn-modern btn-<?= $b['is_active'] ? 'warning' : 'success' ?>"
                                    onclick="toggleStatus(<?= $b['id'] ?>, <?= $b['is_active'] ?>)">
                                <i class="bi bi-<?= $b['is_active'] ? 'pause' : 'play' ?>-circle"></i>
                                <?= $b['is_active'] ? 'Disable' : 'Enable' ?>
                            </button>
                            <button class="btn-modern btn-secondary" onclick="deleteBundle(<?= $b['id'] ?>, '<?= htmlspecialchars($b['name'], ENT_QUOTES) ?>')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-box-seam"></i>
                    <p style="font-size:1.25rem;font-weight:700;color:var(--text-primary);margin-bottom:.5rem;">No bundles yet</p>
                    <p style="color:var(--text-secondary);margin-bottom:1.5rem;">Click "Add Bundle" to create your first resort package.</p>
                    <button class="btn-modern btn-primary" onclick="openAddModal()">
                        <i class="bi bi-plus-circle"></i> Add Bundle
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div><!-- /main-content -->

    <!-- --------------------------------------------------------------
         ADD / EDIT BUNDLE MODAL
    --------------------------------------------------------------- -->
    <div class="modal fade" id="bundleModal" tabindex="-1" aria-labelledby="bundleModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content modal-content-modern">
                <div class="modal-header modal-header-modern">
                    <h5 class="modal-title" id="bundleModalTitle">Add Bundle</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body modal-body-modern">
                    <form id="bundleForm" enctype="multipart/form-data" novalidate>
                        <input type="hidden" id="bundleId" name="bundle_id">

                        <div class="row g-3">
                            <!-- Left column -->
                            <div class="col-lg-7">
                                <div class="mb-3">
                                    <label class="form-label-custom">Bundle Name <span style="color:var(--accent-danger)">*</span></label>
                                    <input type="text" class="form-ctrl" id="bundleName" name="name" placeholder="e.g., Family Adventure Package" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label-custom">Description</label>
                                    <textarea class="form-ctrl" id="bundleDescription" name="description" rows="4"
                                              placeholder="Describe what guests get with this bundle�"></textarea>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-4">
                                        <label class="form-label-custom">Price (&#8369;) <span style="color:var(--accent-danger)">*</span></label>
                                        <input type="number" step="0.01" min="0" class="form-ctrl" id="bundlePrice" name="price" placeholder="0.00" required>
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label-custom">Capacity (guests)</label>
                                        <input type="number" min="1" class="form-ctrl" id="bundleCapacity" name="capacity" value="1">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label-custom">Daily Booking Limit</label>
                                        <input type="number" min="1" class="form-ctrl" id="bundleDailyLimit" name="daily_booking_limit" value="10">
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-6">
                                        <label class="form-label-custom">Start Time <span style="color:var(--accent-danger)">*</span></label>
                                        <input type="time" class="form-ctrl" id="bundleStartTime" name="start_time" step="1" value="15:00:00" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom">Duration (hours) <span style="color:var(--accent-danger)">*</span></label>
                                        <input type="number" min="1" class="form-ctrl" id="bundleDurationHours" name="duration_hours" value="6" placeholder="e.g., 6 or 22" required>
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-6">
                                        <label class="form-label-custom">Overnight Check-In Time</label>
                                        <input type="time" class="form-ctrl" id="bundleCheckInTime" name="check_in_time" value="14:00">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom">Overnight Check-Out Time</label>
                                        <input type="time" class="form-ctrl" id="bundleCheckOutTime" name="check_out_time" value="12:00">
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-6">
                                        <label class="form-label-custom">Status</label>
                                        <select class="form-ctrl" id="bundleStatus" name="is_active">
                                            <option value="1">Available</option>
                                            <option value="0">Unavailable</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom">Bundle Image</label>
                                        <input type="file" class="form-ctrl" id="bundleImage" name="image" accept="image/*">
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-12">
                                        <label class="form-label-custom">Bundle Type <small style="color:var(--text-muted);font-weight:400;">(Select one or both)</small></label>
                                        <div class="d-flex align-items-center gap-4 mt-1" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 0.55rem 0.9rem; min-height: 48px;">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input" type="checkbox" name="tour_types[]" id="bundleTourOvernight" value="overnight" style="cursor: pointer;" checked>
                                                <label class="form-check-label text-white" for="bundleTourOvernight" style="cursor: pointer; user-select: none;">
                                                    <i class="bi bi-moon-stars me-1 text-info"></i> Overnight
                                                </label>
                                            </div>
                                            <div class="form-check mb-0">
                                                <input class="form-check-input" type="checkbox" name="tour_types[]" id="bundleTourDay" value="day_tour" style="cursor: pointer;">
                                                <label class="form-check-label text-white" for="bundleTourDay" style="cursor: pointer; user-select: none;">
                                                    <i class="bi bi-sun me-1 text-warning"></i> Day Tour
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label-custom">Amenities <span style="color:var(--text-muted);font-weight:400;">(comma-separated)</span></label>
                                    <input type="text" class="form-ctrl" id="bundleAmenities" name="amenities"
                                           placeholder="e.g., Free WiFi, Parking, Breakfast included, Pool access">
                                </div>

                                <!-- Image preview -->
                                <div id="imgPreviewWrap" class="d-none mb-3">
                                    <label class="form-label-custom">Image Preview</label>
                                    <img id="imgPreview" src="" alt="Preview"
                                         style="width:100%;max-height:180px;object-fit:cover;border-radius:.75rem;border:1px solid var(--border-color);">
                                </div>
                            </div>

                            <!-- Right column � relations -->
                            <div class="col-lg-5">
                                <!-- Included Activities -->
                                <div class="mb-4">
                                    <div class="check-section-title"><i class="bi bi-activity me-1"></i>Included Activities</div>
                                    <div class="check-scroll" id="activityCheckList">
                                        <?php if ($allActivities): ?>
                                            <?php foreach ($allActivities as $act): ?>
                                            <label class="check-item">
                                                <input type="checkbox" class="act-chk" value="<?= $act['id'] ?>" data-price="<?= $act['price_per_hour'] ?>" onchange="recalcBundleInclusionsTotal()">
                                                <span class="check-item-name"><?= htmlspecialchars($act['name']) ?></span>
                                            </label>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p style="color:var(--text-muted);font-size:.8rem;">No active activities found. Add activities first.</p>
                                        <?php endif; ?>
                                    </div>
                                    <input type="hidden" id="activityIds" name="activity_ids">
                                </div>

                                <!-- Included Room/Cottage -->
                                <div class="mb-3">
                                    <div class="check-section-title"><i class="bi bi-house me-1"></i>Room / Cottage Inclusions</div>
                                    <div class="check-scroll" id="accommodationCheckList">
                                        <?php if ($allAccommodations): ?>
                                            <?php foreach ($allAccommodations as $acc): ?>
                                            <label class="check-item">
                                                <input type="checkbox" class="acc-chk" value="<?= $acc['id'] ?>" data-price="<?= $acc['price_per_night'] ?>" onchange="recalcBundleInclusionsTotal()">
                                                <span class="check-item-name">
                                                    <?= htmlspecialchars($acc['name']) ?>
                                                    <small style="color:var(--text-muted);">(<?= ucfirst($acc['type']) ?>)</small>
                                                </span>
                                            </label>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p style="color:var(--text-muted);font-size:.8rem;">No active accommodations found.</p>
                                        <?php endif; ?>
                                    </div>
                                    <input type="hidden" id="accommodationIds" name="accommodation_ids">
                                </div>
                                
                                <!-- Included Items Total Summary -->
                                <div class="mb-3 p-3" style="background: rgba(157,255,0,0.05); border: 1px solid rgba(157,255,0,0.2); border-radius: 8px;">
                                    <div style="color: #9dff00; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.2rem;"><i class="bi bi-calculator me-1"></i>Total Included Value</div>
                                    <div style="color: #fff; font-size: 1.35rem; font-weight: 700;" id="bundleInclusionsTotal">₱0.00</div>
                                    <div style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.2rem;">(This is the combined regular price of all selected items)</div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer modal-footer-modern">
                    <button type="button" class="btn-modern btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn-modern btn-primary" id="saveBundleBtn" onclick="saveBundle()">
                        <i class="bi bi-check-circle"></i> Save Bundle
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- --------------------------------------------------------------
         DELETE CONFIRMATION MODAL
    --------------------------------------------------------------- -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-modern">
                <div class="modal-header modal-header-modern">
                    <h5 class="modal-title" style="color:#FF5A5F;">
                        <i class="bi bi-exclamation-triangle me-2"></i>Delete Bundle
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body modal-body-modern">
                    <p style="color:#B8C4BE;">Are you sure you want to delete <strong id="deleteBundleName" style="color:#ffffff;"></strong>?</p>
                    <p style="color:#5C6B63;font-size:.875rem;margin-top:.5rem;">This action cannot be undone.</p>
                </div>
                <div class="modal-footer modal-footer-modern">
                    <button type="button" class="btn-modern btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn-modern btn-danger" id="confirmDeleteBtn">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
    /* --------------------------------------------------------------
       Manage Bundles � JS
    -------------------------------------------------------------- */
    'use strict';

    let bundleModal, deleteModal, editingId = null;

    // -- Load sidebar & navbar --------------------------------------
    document.addEventListener('DOMContentLoaded', () => {

        bundleModal = new bootstrap.Modal(document.getElementById('bundleModal'));
        deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

        // Image preview
        document.getElementById('bundleImage').addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const wrap = document.getElementById('imgPreviewWrap');
                const img  = document.getElementById('imgPreview');
                img.src = URL.createObjectURL(file);
                wrap.classList.remove('d-none');
            }
        });
    });

    // -- Open Add Modal ---------------------------------------------
    function openAddModal() {
        editingId = null;
        document.getElementById('bundleModalTitle').textContent = 'Add Bundle';
        document.getElementById('bundleForm').reset();
        document.getElementById('bundleId').value = '';
        document.getElementById('bundleStatus').value = '1';
        document.getElementById('bundleDailyLimit').value = '10';
        document.getElementById('bundleCheckInTime').value = '14:00';
        document.getElementById('bundleCheckOutTime').value = '12:00';
        document.getElementById('imgPreviewWrap').classList.add('d-none');
        clearAllCheckboxes();
        // Default: Overnight checked
        document.getElementById('bundleTourOvernight').checked = true;
        document.getElementById('bundleTourDay').checked = false;
        bundleModal.show();
    }

    // -- Edit Bundle ------------------------------------------------
    async function editBundle(id) {
        editingId = id;
        try {
            const res  = await fetch(`../php/add_edit_bundle.php?action=get&bundle_id=${id}`);
            const data = await res.json();
            if (!data.success) { toast('Failed to load bundle: ' + data.message, 'error'); return; }

            const b = data.bundle;
            document.getElementById('bundleModalTitle').textContent = 'Edit Bundle � ' + b.name;
            document.getElementById('bundleId').value          = b.id;
            document.getElementById('bundleName').value        = b.name;
            document.getElementById('bundleDescription').value = b.description || '';
            document.getElementById('bundlePrice').value       = b.price;
            document.getElementById('bundleCapacity').value    = b.capacity;
            document.getElementById('bundleDailyLimit').value  = b.daily_booking_limit || 10;
            document.getElementById('bundleStartTime').value   = (b.start_time || '').toString().substring(0, 8);
            document.getElementById('bundleDurationHours').value = b.duration_hours || '';
            document.getElementById('bundleCheckInTime').value = b.check_in_time ? b.check_in_time.substring(0, 5) : '14:00';
            document.getElementById('bundleCheckOutTime').value = b.check_out_time ? b.check_out_time.substring(0, 5) : '12:00';
            document.getElementById('bundleAmenities').value   = b.amenities || '';
            document.getElementById('bundleStatus').value      = b.is_active;
            // Pre-check tour type checkboxes
            const bType = (b.bundle_type || 'overnight').toLowerCase();
            document.getElementById('bundleTourOvernight').checked = bType === 'overnight' || bType === 'both';
            document.getElementById('bundleTourDay').checked       = bType === 'day_tour'  || bType === 'both';
            document.getElementById('imgPreviewWrap').classList.add('d-none');

            // Pre-check linked activities
            clearAllCheckboxes();
            const actIds = b.activity_ids || [];
            document.querySelectorAll('.act-chk').forEach(chk => {
                if (actIds.includes(parseInt(chk.value))) chk.checked = true;
            });
            const accIds = b.accommodation_ids || [];
            document.querySelectorAll('.acc-chk').forEach(chk => {
                if (accIds.includes(parseInt(chk.value))) chk.checked = true;
            });

            recalcBundleInclusionsTotal();
            bundleModal.show();
        } catch(e) {
            toast('Network error loading bundle', 'error');
        }
    }

    // -- Save Bundle ------------------------------------------------
    async function saveBundle() {
        const name  = document.getElementById('bundleName').value.trim();
        const price = document.getElementById('bundlePrice').value;
        if (!name)  { toast('Bundle name is required', 'error'); return; }
        if (!price || parseFloat(price) < 0) { toast('Please enter a valid price', 'error'); return; }
        const startTime = document.getElementById('bundleStartTime').value;
        const durationHours = parseInt(document.getElementById('bundleDurationHours').value, 10);
        if (!startTime) { toast('Start time is required', 'error'); return; }
        if (!durationHours || durationHours < 1) { toast('Duration hours must be at least 1', 'error'); return; }

        // Collect checked activities & accommodations
        const actIds = [...document.querySelectorAll('.act-chk:checked')].map(c => c.value).join(',');
        const accIds = [...document.querySelectorAll('.acc-chk:checked')].map(c => c.value).join(',');
        document.getElementById('activityIds').value      = actIds;
        document.getElementById('accommodationIds').value = accIds;

        const btn = document.getElementById('saveBundleBtn');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving�';
        btn.disabled  = true;

        const form     = document.getElementById('bundleForm');
        const formData = new FormData(form);
        formData.set('action', editingId ? 'edit' : 'add');

        try {
            const res  = await fetch('../php/add_edit_bundle.php', { method:'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                bundleModal.hide();
                toast(data.message, 'success');
                setTimeout(() => location.reload(), 900);
            } else {
                toast(data.message || 'Save failed', 'error');
            }
        } catch(e) {
            toast('Network error. Please try again.', 'error');
        } finally {
            btn.innerHTML = '<i class="bi bi-check-circle"></i> Save Bundle';
            btn.disabled  = false;
        }
    }

    // -- Toggle Available/Unavailable -------------------------------
    async function toggleStatus(id, current) {
        const action = current ? 'disable' : 'enable';
        if (!confirm(`Are you sure you want to ${action} this bundle?`)) return;
        const fd = new FormData();
        fd.append('action', 'toggle_status');
        fd.append('bundle_id', id);
        try {
            const res  = await fetch('../php/add_edit_bundle.php', { method:'POST', body: fd });
            const data = await res.json();
            if (data.success) { toast('Bundle status updated', 'success'); setTimeout(() => location.reload(), 700); }
            else toast(data.message, 'error');
        } catch { toast('Network error', 'error'); }
    }

    // -- Delete bundle ----------------------------------------------
    function deleteBundle(id, name) {
        document.getElementById('deleteBundleName').textContent = name;
        document.getElementById('confirmDeleteBtn').onclick = async () => {
            const fd = new FormData();
            fd.append('action', 'delete');
            fd.append('bundle_id', id);
            try {
                const res  = await fetch('../php/add_edit_bundle.php', { method:'POST', body: fd });
                const data = await res.json();
                deleteModal.hide();
                if (data.success) { toast('Bundle deleted', 'success'); setTimeout(() => location.reload(), 700); }
                else toast(data.message, 'error');
            } catch { toast('Network error', 'error'); }
        };
        deleteModal.show();
    }

    // -- Helpers ----------------------------------------------------
    function recalcBundleInclusionsTotal() {
        let total = 0;
        document.querySelectorAll('.act-chk:checked, .acc-chk:checked').forEach(chk => {
            total += parseFloat(chk.getAttribute('data-price')) || 0;
        });
        document.getElementById('bundleInclusionsTotal').textContent = '₱' + total.toLocaleString('en-PH', {minimumFractionDigits: 2});
    }

    function clearAllCheckboxes() {
        document.querySelectorAll('.act-chk, .acc-chk').forEach(c => c.checked = false);
        recalcBundleInclusionsTotal();
    }

    function toast(msg, type = 'success') {
        const wrap = document.getElementById('toastWrap');
        const el   = document.createElement('div');
        el.className = `toast-item ${type === 'error' ? 'error' : 'success'}`;
        el.innerHTML = `<i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'}"></i>${msg}`;
        wrap.appendChild(el);
        setTimeout(() => el.remove(), 4000);
    }
    </script>
</body>
</html>




