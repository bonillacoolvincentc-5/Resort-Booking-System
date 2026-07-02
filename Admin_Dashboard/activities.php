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
        a.min_duration_hours,
        a.max_duration_hours,
        a.capacity,
        a.image_url,
        a.is_active,
        a.created_at,
        COUNT(b.id) as total_bookings
    FROM activities a
    LEFT JOIN bookings b ON a.id = b.activity_id
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activities Management - YP4K Resort</title>
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
                <h1 class="page-title">Activities Management</h1>
                <p class="text-muted">Manage resort activities and facilities</p>
            </div>
            <div class="page-actions">
                <button class="btn-modern btn-primary" onclick="openAddActivityModal()">
                    <i class="bi bi-plus-circle"></i>
                    Add Activity
                </button>
            </div>
        </div>

        <div class="activities-grid">
            <?php if (count($activities) > 0): ?>
                <?php foreach ($activities as $activity): ?>
                    <?php
                        // Determine status based on is_active
                        $statusClass = $activity['is_active'] ? 'status-active' : 'status-inactive';
                        $statusText = $activity['is_active'] ? 'Active' : 'Inactive';
                        
                        // Static fallback icon
                        $icon = 'bi-activity';
                        
                        // Format price
                        $price = number_format($activity['price_per_hour'], 2);
                    ?>
                    <div class="activity-card">
                        <div class="activity-image">
                            <?php if ($activity['image_url']): ?>
                                <img src="<?php echo htmlspecialchars($activity['image_url']); ?>" alt="<?php echo htmlspecialchars($activity['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <i class="bi <?php echo $icon; ?>"></i>
                            <?php endif; ?>
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
                                    <div class="stat-value">?<?php echo $price; ?></div>
                                    <div class="stat-label">Per Hour</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value"><?php echo $activity['total_bookings']; ?></div>
                                    <div class="stat-label">Total Bookings</div>
                                </div>
                            </div>
                            <div class="activity-actions">
                                <button class="btn-modern btn-primary" onclick="editActivity(<?php echo $activity['id']; ?>)">
                                    <i class="bi bi-pencil"></i>
                                    Edit
                                </button>
                                <button class="btn-modern btn-secondary" onclick="deleteActivity(<?php echo $activity['id']; ?>)">
                                    <i class="bi bi-trash"></i>
                                    Delete
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

    <!-- Add/Edit Activity Modal -->
    <div class="modal fade" id="activityModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modal-content-modern">
                <div class="modal-header modal-header-modern">
                    <h5 class="modal-title" id="activityModalTitle">Add Activity</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body modal-body-modern">
                    <form id="activityForm">
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
                                    <label class="form-label-modern">Price per Hour (₱)</label>
                                    <input type="number" step="0.01" class="form-control-modern" id="activityPrice" name="price_per_hour" required>
                                </div>
                            </div>
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
            try {
                const response = await fetch(`../php/add_edit_activity.php?action=get&activity_id=${activityId}`);
                const result = await response.json();

                if (result.success) {
                    const activity = result.activity;
                    
                    document.getElementById('activityModalTitle').textContent = 'Edit Activity';
                    document.getElementById('activityId').value = activity.id;
                    document.getElementById('activityName').value = activity.name;
                    document.getElementById('activityDescription').value = activity.description || '';
                    document.getElementById('activityPrice').value = activity.price_per_hour;
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

        // View Analytics (placeholder)
        function viewAnalytics(activityId) {
            alert('Analytics feature coming soon for activity ID: ' + activityId);
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>



