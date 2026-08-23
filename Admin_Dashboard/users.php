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

// Fetch real users from database
$query = "
    SELECT 
        u.*,
        COUNT(b.id) as booking_count
    FROM users u
    LEFT JOIN bookings b ON u.id = b.user_id
    GROUP BY u.id
    ORDER BY u.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - YP4K Resort</title>
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

    <div id="mainContent" class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title">User Management</h1>
                <p class="text-muted">Manage all registered users and their permissions</p>
            </div>
            <div class="page-actions">
                <button class="btn-modern btn-primary">
                    <i class="bi bi-person-plus"></i> Add User
                </button>
            </div>
        </div>

        <div class="users-container">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Bookings</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $index => $user): ?>
                            <?php
                                // Generate initials from name
                                $fullName = trim($user['first_name'] . ' ' . $user['middle_name'] . ' ' . $user['last_name']);
                                $initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
                                
                                // Determine status based on is_verified
                                $statusClass = $user['is_verified'] ? 'status-active' : 'status-inactive';
                                $statusText = $user['is_verified'] ? 'Active' : 'Inactive';
                                
                                // Format date
                                $joinedDate = date('M j, Y', strtotime($user['created_at']));
                                
                                // Avatar colors (cycle through predefined gradients)
                                $gradients = [
                                    'linear-gradient(135deg, var(--accent-primary), var(--accent-secondary))',
                                    'linear-gradient(135deg, var(--accent-success), #059669)',
                                    'linear-gradient(135deg, var(--accent-warning), #d97706)',
                                    'linear-gradient(135deg, var(--accent-danger), #dc2626)',
                                    'linear-gradient(135deg, var(--accent-secondary), #6366f1)'
                                ];
                                $avatarGradient = $gradients[$index % count($gradients)];
                            ?>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar" style="background: <?php echo $avatarGradient; ?>;">
                                            <?php echo $initials; ?>
                                        </div>
                                        <div class="user-details">
                                            <div class="user-name"><?php echo htmlspecialchars($fullName); ?></div>
                                            <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>Customer</td>
                                <td><?php echo $joinedDate; ?></td>
                                <td><?php echo $user['booking_count']; ?></td>
                                <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-action btn-view" onclick="viewUser(<?php echo $user['id']; ?>)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn-action btn-delete" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem;">
                                <i class="bi bi-inbox" style="font-size: 2rem; color: var(--text-muted);"></i>
                                <p style="color: var(--text-secondary); margin-top: 0.5rem;">No users found</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- View User Details Modal -->
    <div class="modal fade" id="viewUserModal" tabindex="-1" aria-labelledby="viewUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="background: #101B17 !important; border: 1px solid rgba(255,255,255,0.1) !important; border-radius: 1rem !important; overflow: hidden; box-shadow: 0 25px 50px rgba(0,0,0,0.8) !important;">
                <div class="modal-header" style="border-bottom: 1px solid rgba(255,255,255,0.1) !important; background: #15221D !important; padding: 1.5rem;">
                    <h5 class="modal-title d-flex align-items-center gap-2" id="viewUserModalLabel" style="color: #fff !important; font-weight: 700;">
                        <i class="bi bi-person-badge text-primary" style="font-size: 1.5rem;"></i> User Account Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" style="color: #B8C4BE !important; background: #101B17 !important;">
                    <div class="row g-4">
                        <div class="col-md-4 text-center d-flex flex-column align-items-center justify-content-center" style="border-right: 1px solid var(--border-color);">
                            <div class="avatar-circle mb-3" style="width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary)); display: flex; align-items: center; justify-content: center; font-size: 2.5rem; color: #fff; box-shadow: 0 10px 20px rgba(0,0,0,0.2);">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <h4 id="modalUserName" class="mb-1" style="font-weight: 700; color: #fff;">---</h4>
                            <span id="modalUserStatus" class="badge rounded-pill mb-2 px-3 py-2">---</span>
                            <p id="modalUserEmail" class="text-muted small mb-0">---</p>
                        </div>
                        <div class="col-md-8">
                            <h6 class="text-uppercase text-muted small fw-bold mb-3" style="letter-spacing: 1px; color: var(--accent-primary) !important;">Personal Information</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-sm-6">
                                    <label class="text-muted small d-block">Full Name</label>
                                    <span id="modalFullName" class="fw-medium text-white">---</span>
                                </div>
                                <div class="col-sm-6">
                                    <label class="text-muted small d-block">Phone Number</label>
                                    <span id="modalPhone" class="fw-medium text-white">---</span>
                                </div>
                                <div class="col-sm-6">
                                    <label class="text-muted small d-block">Birthdate</label>
                                    <span id="modalBirthdate" class="fw-medium text-white">---</span>
                                </div>
                                <div class="col-sm-6">
                                    <label class="text-muted small d-block">Gender</label>
                                    <span id="modalGender" class="fw-medium text-white">---</span>
                                </div>
                                <div class="col-12">
                                    <label class="text-muted small d-block">Complete Address</label>
                                    <span id="modalAddress" class="fw-medium text-white">---</span>
                                </div>
                            </div>

                            <h6 class="text-uppercase text-muted small fw-bold mb-3" style="letter-spacing: 1px; color: var(--accent-primary) !important;">Account Activity</h6>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="text-muted small d-block">Date Joined</label>
                                    <span id="modalJoined" class="fw-medium text-white">---</span>
                                </div>
                                <div class="col-sm-6">
                                    <label class="text-muted small d-block">Total Bookings</label>
                                    <span id="modalBookings" class="fw-medium badge bg-primary px-3 py-1">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid rgba(255,255,255,0.1) !important; background: #15221D !important; padding: 1rem 1.5rem;">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" style="border-radius: 0.5rem;">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const usersData = <?php echo json_encode($users); ?>;

        function viewUser(userId) {
            const user = usersData.find(u => u.id == userId);
            if (!user) return;
            
            // Construct full name
            const fname = user.first_name || '';
            const mname = user.middle_name ? ' ' + user.middle_name + ' ' : ' ';
            const lname = user.last_name || '';
            const sfx = user.suffix ? ' ' + user.suffix : '';
            const fullName = (fname + mname + lname + sfx).trim().replace(/\s+/g, ' ') || 'No Name';

            // Construct address
            let address = user.address || '';
            if (!address || /\d{8,}/.test(address)) {
                const parts = [user.barangay, user.city_municipality, user.province, user.region].filter(Boolean);
                address = parts.join(', ') || 'No Address Provided';
            }

            document.getElementById('modalUserName').textContent = fullName;
            document.getElementById('modalFullName').textContent = fullName;
            document.getElementById('modalUserEmail').textContent = user.email || 'N/A';
            document.getElementById('modalPhone').textContent = user.phone_number || 'N/A';
            document.getElementById('modalBirthdate').textContent = user.birthdate || 'Not specified';
            document.getElementById('modalGender').textContent = user.gender || 'Not specified';
            document.getElementById('modalAddress').textContent = address;
            document.getElementById('modalBookings').textContent = user.booking_count || '0';

            // Date joined format
            if (user.created_at) {
                const d = new Date(user.created_at);
                document.getElementById('modalJoined').textContent = d.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            } else {
                document.getElementById('modalJoined').textContent = 'N/A';
            }

            // Status badge
            const statusBadge = document.getElementById('modalUserStatus');
            if (user.is_verified == 1 || user.is_verified == '1') {
                statusBadge.textContent = 'Verified';
                statusBadge.className = 'badge rounded-pill mb-2 px-3 py-2 bg-success';
            } else {
                statusBadge.textContent = 'Unverified';
                statusBadge.className = 'badge rounded-pill mb-2 px-3 py-2 bg-warning text-dark';
            }

            const modal = new bootstrap.Modal(document.getElementById('viewUserModal'));
            modal.show();
        }

        // Table interactions
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-delete')) {
                if (confirm('Are you sure you want to delete this user?')) {
                    alert('User deleted');
                }
            }
        });
    </script>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>



