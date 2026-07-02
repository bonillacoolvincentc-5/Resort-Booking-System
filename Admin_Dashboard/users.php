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
        u.id,
        u.first_name,
        u.middle_name,
        u.last_name,
        u.email,
        u.phone_number,
        u.is_verified,
        u.created_at,
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
        <div class="page-header">
            <div>
                <h1 class="h3 mb-2">User Management</h1>
                <p class="text-muted mb-0">Manage all registered users and their permissions</p>
            </div>
            <button class="btn-modern btn-primary">
                <i class="bi bi-person-plus"></i>
                Add User
            </button>
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
                                        <button class="btn-action btn-edit" onclick="editUser(<?php echo $user['id']; ?>)">
                                            <i class="bi bi-pencil"></i>
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

    <script>

        // Table interactions
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-view')) {
                alert('View user details');
            }
            if (e.target.closest('.btn-edit')) {
                alert('Edit user');
            }
            if (e.target.closest('.btn-delete')) {
                if (confirm('Are you sure you want to delete this user?')) {
                    alert('User deleted');
                }
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>



