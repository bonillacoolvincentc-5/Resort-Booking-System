<?php
/**
 * Staff Login Process — YP4K Resort
 * Authenticates against admins table WHERE role = 'staff'
 */
session_start();
require_once '../phpAuthentication/db_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Authentication/staff-login.php');
    exit();
}

$email    = trim($_POST['email']    ?? '');
$password = $_POST['password'] ?? '';

try {
    $stmt = $conn->prepare(
        "SELECT id, first_name, last_name, email, password FROM admins WHERE email = ? AND role = 'staff'"
    );
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $staff  = $result->fetch_assoc();

    if ($staff && password_verify($password, $staff['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id']    = $staff['id'];
        $_SESSION['admin_email'] = $staff['email'];
        $_SESSION['admin_name']  = $staff['first_name'] . ' ' . $staff['last_name'];
        $_SESSION['is_staff']    = true;
        $_SESSION['login_time']  = time();
        header('Location: ../Staff_Dashboard/bookings.php');
        exit();
    } else {
        $error = "Invalid email or password.";
        header('Location: ../Authentication/staff-login.php?error=' . urlencode($error));
        exit();
    }
} catch (Throwable $e) {
    error_log("Staff login error: " . $e->getMessage());
    $error = "Login failed. Please try again.";
    header('Location: ../Authentication/staff-login.php?error=' . urlencode($error));
    exit();
}
