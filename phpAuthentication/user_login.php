<?php
/**
 * YP4K Resort - User Login
 * Handles user authentication with secure password verification
 */

// Include database configuration
require_once 'db_config.php';

// Start session for login state
session_start();

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize input data
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) ? $_POST['remember'] : false;
    
    // Basic validation
    $errors = [];
    
    if (empty($email)) $errors[] = 'Email is required';
    if (empty($password)) $errors[] = 'Password is required';
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    // If there are validation errors, return them
    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors
        ]);
        exit;
    }
    
    try {
        // Find user by email
        $stmt = $db->prepare("SELECT id, first_name, last_name, email, phone_number, password, created_at FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Password is correct, create session
                session_regenerate_id(true); // Prevent session fixation
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_phone'] = $user['phone_number'];
                $_SESSION['logged_in'] = true;
                $_SESSION['login_time'] = time();
                
                // Handle "Remember Me" functionality
                if ($remember) {
                    // Generate remember token
                    $token = bin2hex(random_bytes(32));
                    $expires = time() + (30 * 24 * 60 * 60); // 30 days
                    
                    // Store token in database (you would need a remember_tokens table)
                    setcookie('remember_token', $token, $expires, '/', '', false, true);
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful!',
                    'redirect' => '../index-new.html',
                    'user' => [
                        'id' => $user['id'],
                        'name' => $user['first_name'] . ' ' . $user['last_name'],
                        'email' => $user['email']
                    ]
                ]);
                
            } else {
                // Password is incorrect
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid email or password'
                ]);
            }
            
        } else {
            // User not found
            echo json_encode([
                'success' => false,
                'message' => 'Invalid email or password'
            ]);
        }
        
    } catch (PDOException $e) {
        // Log error for debugging
        error_log("Login error: " . $e->getMessage());
        
        echo json_encode([
            'success' => false,
            'message' => 'Database error. Please try again later.'
        ]);
    }
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>
