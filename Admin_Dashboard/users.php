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

        .users-container {
            background: var(--bg-card);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            overflow: hidden;
        }

        .table-modern {
            width: 100%;
            border-collapse: collapse;
        }

        .table-modern thead {
            background: var(--bg-tertiary);
        }

        .table-modern th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--border-color);
        }

        .table-modern td {
            padding: 1rem;
            color: var(--text-secondary);
            font-size: 0.9rem;
            border-bottom: 1px solid rgba(51, 65, 85, 0.2);
        }

        .table-modern tbody tr {
            transition: all 0.3s ease;
        }

        .table-modern tbody tr:hover {
            background: rgba(59, 130, 246, 0.05);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.125rem;
        }

        .user-email {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .status-active {
            background: rgba(16, 185, 129, 0.2);
            color: var(--accent-success);
        }

        .status-inactive {
            background: rgba(239, 68, 68, 0.2);
            color: var(--accent-danger);
        }

        .status-banned {
            background: rgba(245, 158, 11, 0.2);
            color: var(--accent-warning);
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-action {
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-view {
            background: rgba(59, 130, 246, 0.1);
            color: var(--accent-primary);
        }

        .btn-view:hover {
            background: var(--accent-primary);
            color: white;
        }

        .btn-edit {
            background: rgba(245, 158, 11, 0.1);
            color: var(--accent-warning);
        }

        .btn-edit:hover {
            background: var(--accent-warning);
            color: white;
        }

        .btn-delete {
            background: rgba(239, 68, 68, 0.1);
            color: var(--accent-danger);
        }

        .btn-delete:hover {
            background: var(--accent-danger);
            color: white;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
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
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .table-modern {
                font-size: 0.8rem;
            }

            .table-modern th,
            .table-modern td {
                padding: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div id="sidebar-container"></div>
    <div id="navbar-container"></div>

    <div class="main-content">
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
