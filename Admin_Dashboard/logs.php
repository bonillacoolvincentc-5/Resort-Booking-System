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

global $conn;

// Fetch activity logs
$query = "
    SELECT 
        l.*,
        COALESCE(CONCAT(a.first_name, ' ', a.last_name), 'Unknown') as user_name
    FROM activity_logs l
    LEFT JOIN admins a ON l.user_id = a.id
    ORDER BY l.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

$logs = [];
while ($row = $result->fetch_assoc()) {
    $logs[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs - YP4K Resort</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/resort-theme.css">
    <link rel="stylesheet" href="../css/dashboard-resort.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="components/sidebar.css">
    <link rel="stylesheet" href="components/navbar.css">
    <link rel="stylesheet" href="../css/admin-theme.css">
    <script src="components/admin-components.js" defer></script>
    <style>
        .role-badge-admin {
            background-color: rgba(59, 130, 246, 0.15); /* Blue background */
            color: #60a5fa; /* Blue text */
            border: 1px solid rgba(59, 130, 246, 0.3);
        }
        .role-badge-admin::before {
            background-color: #60a5fa !important;
        }
        
        .role-badge-staff {
            background-color: rgba(168, 85, 247, 0.15); /* Purple background */
            color: #c084fc; /* Purple text */
            border: 1px solid rgba(168, 85, 247, 0.3);
        }
        .role-badge-staff::before {
            background-color: #c084fc !important;
        }
    </style>
</head>
<body>
    <div id="sidebar-container"></div>
    <div id="navbar-container"></div>

    <div id="mainContent" class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title">Activity Logs</h1>
                <p class="text-muted">Monitor admin and staff actions such as walk-in bookings and status updates.</p>
            </div>
        </div>

        <div class="users-container">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($logs) > 0): ?>
                        <?php foreach ($logs as $log): ?>
                            <?php
                                $actionType = $log['action_type'];
                                $dateStr = date('M j, Y h:i A', strtotime($log['created_at']));
                                $roleClass = strtolower($log['role']) === 'admin' ? 'role-badge-admin' : 'role-badge-staff';
                            ?>
                            <tr>
                                <td>
                                    <div class="user-details">
                                        <div class="user-name"><?php echo htmlspecialchars($log['user_name']); ?></div>
                                    </div>
                                </td>
                                <td><span class="status-badge <?php echo $roleClass; ?>"><?php echo ucfirst($log['role']); ?></span></td>
                                <td><strong><?php echo ucwords(str_replace('_', ' ', $actionType)); ?></strong></td>
                                <td style="color: var(--text-secondary);"><?php echo htmlspecialchars($log['description']); ?></td>
                                <td style="font-size: 0.9rem; color: var(--text-muted);"><?php echo $dateStr; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem;">
                                <i class="bi bi-inbox" style="font-size: 2rem; color: var(--text-muted);"></i>
                                <p style="color: var(--text-secondary); margin-top: 0.5rem;">No activity logs found</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
