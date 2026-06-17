<?php
/*
 * Admin Dashboard Configuration
 * YP4K Resort - Booking & Trail Guide System
 * Centralized path and configuration management
 */

// Base paths
define('ADMIN_ROOT', __DIR__);
define('PROJECT_ROOT', dirname(ADMIN_ROOT));
define('PHP_AUTH_ROOT', PROJECT_ROOT . '/phpAuthentication');
define('PHP_ROOT', PROJECT_ROOT . '/php');
define('AUTHENTICATION_ROOT', PROJECT_ROOT . '/Authentication');
define('USER_DASHBOARD_ROOT', PROJECT_ROOT . '/User_Dashboard');

// Database paths
define('DB_CONFIG_PATH', PHP_AUTH_ROOT . '/db_config.php');
define('SESSION_HELPER_PATH', PHP_AUTH_ROOT . '/session_helper.php');
define('EMAIL_HELPER_PATH', PHP_AUTH_ROOT . '/email_helper.php');

// Authentication paths
define('ADMIN_LOGIN_PATH', AUTHENTICATION_ROOT . '/admin-login.php');
define('USER_LOGIN_PATH', AUTHENTICATION_ROOT . '/login.php');
define('ADMIN_REGISTER_PATH', AUTHENTICATION_ROOT . '/admin-register.php');
define('USER_REGISTER_PATH', AUTHENTICATION_ROOT . '/register.php');

// Process paths
define('USER_REGISTER_PROCESS_PATH', PHP_ROOT . '/user_register_process.php');
define('ADMIN_REGISTER_PROCESS_PATH', PHP_ROOT . '/admin_register_process.php');
define('USER_LOGIN_PROCESS_PATH', PHP_ROOT . '/user_login.php');
define('ADMIN_LOGIN_PROCESS_PATH', PHP_ROOT . '/admin_login_process.php');

// Dashboard paths
define('USER_DASHBOARD_PATH', USER_DASHBOARD_ROOT . '/dashboard.php');

// Helper function for includes
function admin_include($file) {
    $full_path = '';
    
    // Check if it's a core helper file
    if (strpos($file, 'db_config') !== false) {
        $full_path = DB_CONFIG_PATH;
    } elseif (strpos($file, 'session_helper') !== false) {
        $full_path = SESSION_HELPER_PATH;
    } elseif (strpos($file, 'email_helper') !== false) {
        $full_path = EMAIL_HELPER_PATH;
    } else {
        // Default to phpAuthentication folder
        $full_path = PHP_AUTH_ROOT . '/' . $file;
    }
    
    if (file_exists($full_path)) {
        require_once $full_path;
        return true;
    }
    
    return false;
}

// Helper function for redirects
function admin_redirect($page) {
    header('Location: ' . ADMIN_ROOT . '/' . $page);
    exit();
}

// Helper function for URL generation
function admin_url($page) {
    return './' . $page;
}

// Helper function for asset URLs
function admin_asset($path) {
    return './' . $path;
}
?>
