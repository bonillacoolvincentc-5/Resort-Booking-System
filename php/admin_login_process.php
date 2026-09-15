<?php
session_start();
require_once '../phpAuthentication/db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    try {
        // Check if it's an admin login
        $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password, role, created_at FROM admins WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        
        if ($admin && password_verify($password, $admin['password'])) {
            // Admin login successful
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_name'] = $admin['first_name'] . ' ' . $admin['last_name'];
            
            $role = $admin['role'] ?? 'admin';
            $_SESSION['admin_role'] = $role;
            $_SESSION['login_time'] = time();
            
            if ($role === 'staff') {
                $_SESSION['is_staff'] = true;
                header('Location: ../Staff_Dashboard/bookings.php');
            } else {
                $_SESSION['is_admin'] = true;
                header('Location: ../Admin_Dashboard/dashboard.php');
            }
            exit();
        } else {
            // Check if it's a regular user (to show appropriate error)
            $user_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $user_stmt->bind_param("s", $email);
            $user_stmt->execute();
            $user_result = $user_stmt->get_result();
            $user = $user_result->fetch_assoc();
            
            if ($user) {
                $error = "This email is registered as a user account. Please use the user login.";
            } else {
                $error = "Invalid email or password";
            }
            
            header('Location: ../Authentication/admin-login.php?error=' . urlencode($error));
            exit();
        }
    } catch (Throwable $e) {
        error_log("Admin login error: " . $e->getMessage());
        $error = "Login failed. Please try again.";
        header('Location: ../Authentication/admin-login.php?error=' . urlencode($error));
        exit();
    }
} else {
    header('Location: ../Authentication/admin-login.php');
    exit();
}

$conn->close();
?>
