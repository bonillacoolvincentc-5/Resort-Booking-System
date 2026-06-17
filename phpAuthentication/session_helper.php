<?php
/**
 * YP4K Resort - Session Management
 * Helper functions for session management and authentication
 */

// Include database configuration
require_once 'db_config.php';

// Start secure session
function start_secure_session() {
    $session_name = 'yp4k_session';
    $secure = true; // Set to true if using HTTPS
    $httponly = true; // Prevent JavaScript access to session cookie
    
    // Set session cookie parameters
    session_set_cookie_params([
        'lifetime' => 0, // Session cookie
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => 'Strict'
    ]);
    
    session_name($session_name);
    session_start();
    
    // Regenerate session ID to prevent fixation
    if (!isset($_SESSION['initialized'])) {
        session_regenerate_id(true);
        $_SESSION['initialized'] = true;
    }
}

// Check if user is logged in
function is_user_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// Check if admin is logged in
function is_admin_logged_in() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

// Get current user data
function get_current_user() {
    if (is_user_logged_in()) {
        return [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'],
            'name' => $_SESSION['user_name'],
            'phone' => $_SESSION['user_phone'] ?? '',
            'login_time' => $_SESSION['login_time'] ?? 0
        ];
    }
    return null;
}

// Get current admin data
function get_current_admin() {
    if (is_admin_logged_in()) {
        return [
            'id' => $_SESSION['admin_id'],
            'email' => $_SESSION['admin_email'],
            'name' => $_SESSION['admin_name'],
            'login_time' => $_SESSION['login_time'] ?? 0
        ];
    }
    return null;
}

// Logout user
function logout_user() {
    // Unset all session variables
    $_SESSION = [];
    
    // Delete session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    
    // Destroy session
    session_destroy();
}

// Require user login (redirect if not logged in)
function require_user_login() {
    if (!is_user_logged_in()) {
        header('Location: ../Authentication/login.php');
        exit();
    }
}

// Require admin login (redirect if not logged in)
function require_admin_login() {
    if (!is_admin_logged_in()) {
        header('Location: ../Authentication/admin-login.php');
        exit();
    }
}

// Sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Generate CSRF token
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Validate password strength
function validate_password_strength($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter';
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter';
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number';
    }
    
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $errors[] = 'Password must contain at least one special character';
    }
    
    return $errors;
}
?>
