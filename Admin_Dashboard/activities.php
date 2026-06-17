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
        a.icon,
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

        .activities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .activity-card {
            background: var(--bg-card);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .activity-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border-color: var(--accent-primary);
        }

        .activity-image {
            height: 200px;
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }

        .activity-content {
            padding: 1.5rem;
        }

        .activity-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .activity-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }

        .activity-status {
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-active {
            background: rgba(16, 185, 129, 0.2);
            color: var(--accent-success);
        }

        .status-inactive {
            background: rgba(239, 68, 68, 0.2);
            color: var(--accent-danger);
        }

        .activity-description {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 1rem;
            line-height: 1.5;
        }

        .activity-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .stat-item {
            text-align: center;
            padding: 0.75rem;
            background: var(--bg-tertiary);
            border-radius: 0.5rem;
        }

        .stat-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--accent-primary);
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .activity-actions {
            display: flex;
            gap: 0.5rem;
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
            font-size: 0.875rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            color: white;
        }

        .btn-secondary {
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }

        .btn-primary:hover,
        .btn-secondary:hover {
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .activities-grid {
                grid-template-columns: 1fr;
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
                <h1 class="h3 mb-2">Activities Management</h1>
                <p class="text-muted mb-0">Manage resort activities and facilities</p>
            </div>
            <button class="btn-modern btn-primary" onclick="openAddActivityModal()">
                <i class="bi bi-plus-circle"></i>
                Add Activity
            </button>
        </div>

        <div class="activities-grid">
            <?php if (count($activities) > 0): ?>
                <?php foreach ($activities as $activity): ?>
                    <?php
                        // Determine status based on is_active
                        $statusClass = $activity['is_active'] ? 'status-active' : 'status-inactive';
                        $statusText = $activity['is_active'] ? 'Active' : 'Inactive';
                        
                        // Use icon from database or default
                        $icon = $activity['icon'] ? $activity['icon'] : 'bi-activity';
                        
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
                                    <div class="stat-value">₱<?php echo $price; ?></div>
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
            <div class="modal-content" style="background: var(--bg-card); backdrop-filter: blur(20px); border: 1px solid var(--border-color); border-radius: 1rem;">
                <div class="modal-header" style="background: var(--bg-tertiary); border-bottom: 1px solid var(--border-color); padding: 1.5rem; border-radius: 1rem 1rem 0 0;">
                    <h5 class="modal-title" style="color: var(--text-primary);" id="activityModalTitle">Add Activity</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding: 1.5rem;">
                    <form id="activityForm">
                        <input type="hidden" id="activityId" name="activity_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" style="color: var(--text-primary); font-weight: 500;">Activity Name</label>
                                    <input type="text" class="form-control" id="activityName" name="name" required style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary);">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" style="color: var(--text-primary); font-weight: 500;">Price per Hour (₱)</label>
                                    <input type="number" step="0.01" class="form-control" id="activityPrice" name="price_per_hour" required style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary);">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="color: var(--text-primary); font-weight: 500;">Description</label>
                            <textarea class="form-control" id="activityDescription" name="description" rows="3" style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary);"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" style="color: var(--text-primary); font-weight: 500;">Icon (Bootstrap class)</label>
                                    <input type="text" class="form-control" id="activityIcon" name="icon" placeholder="e.g., bi-basketball" style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary);">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" style="color: var(--text-primary); font-weight: 500;">Status</label>
                                    <select class="form-select" id="activityStatus" name="is_active" style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary);">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="color: var(--text-primary); font-weight: 500;">Activity Image</label>
                            <input type="file" class="form-control" id="activityImage" name="image" accept="image/*" style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary);">
                            <small class="text-muted" style="color: var(--text-muted);">Leave empty to keep existing image (when editing)</small>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" style="color: var(--text-primary); font-weight: 500;">Min Duration (hours)</label>
                                    <input type="number" step="0.5" class="form-control" id="minDuration" name="min_duration_hours" value="1.0" style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary);">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" style="color: var(--text-primary); font-weight: 500;">Max Duration (hours)</label>
                                    <input type="number" step="0.5" class="form-control" id="maxDuration" name="max_duration_hours" value="4.0" style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary);">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" style="color: var(--text-primary); font-weight: 500;">Capacity</label>
                                    <input type="number" class="form-control" id="activityCapacity" name="capacity" value="10" style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary);">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer" style="background: var(--bg-tertiary); border-top: 1px solid var(--border-color); padding: 1.5rem; border-radius: 0 0 1rem 1rem;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary);">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveActivity()" style="background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary)); border: none;">Save Activity</button>
                </div>
            </div>
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
                    document.getElementById('activityIcon').value = activity.icon || '';
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
