<?php
/*
 * User Login Process
 * YP4K Resort - Booking & Trail Guide System
 * Handles user login with role-based redirection
 */

session_start();
require_once '../phpAuthentication/db_config.php';

header('Content-Type: application/json');

// Function to send JSON response and exit
function sendJsonResponse($success, $message, $data = []) {
    $response = array_merge(['success' => $success, 'message' => $message], $data);
    echo json_encode($response);
    exit();
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    try {
        // Get form data
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $remember = isset($_POST['remember']);
        
        // Validate input
        if (empty($email) || empty($password)) {
            sendJsonResponse(false, "Email and password are required!");
        }
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            sendJsonResponse(false, "Invalid email format!");
        }
        
        // Check user in users table
        $user_stmt = $conn->prepare("
            SELECT id, first_name, last_name, email, phone_number, password, is_verified 
            FROM users WHERE email = ?
        ");
        $user_stmt->bind_param("s", $email);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        
        if ($user_result->num_rows === 1) {
            $user = $user_result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Check if email is verified
                if ($user['is_verified'] == 0) {
                    sendJsonResponse(false, "Please verify your email first! Check your inbox for the verification code.", [
                        'requires_verification' => true
                    ]);
                }
                
                // Password is correct and email is verified, set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_type'] = 'user';
                $_SESSION['login_time'] = time();
                $_SESSION['is_authenticated'] = true;
                
                // Set remember me cookie if checked
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $cookie_time = time() + (30 * 24 * 60 * 60); // 30 days
                    
                    // Update user with remember token
                    $update_stmt = $conn->prepare("UPDATE users SET remember_token = ?, token_expiry = FROM_UNIXTIME(?) WHERE id = ?");
                    $update_stmt->bind_param("ssi", $token, $cookie_time, $user['id']);
                    $update_stmt->execute();
                    
                    // Set cookie
                    setcookie('remember_token', $token, $cookie_time, '/', '', true, true);
                }
                
                // Log the login
                $log_stmt = $conn->prepare("
                    INSERT INTO login_logs (user_id, user_type, email, ip_address, user_agent, login_time) 
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
                $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
                $log_stmt->bind_param("issss", $user['id'], 'user', $email, $ip_address, $user_agent);
                $log_stmt->execute();
                $log_stmt->close();
                
                sendJsonResponse(true, 'Login successful!', [
                    'redirect' => '../User_Dashboard/create-booking.php'
                ]);
                
            } else {
                sendJsonResponse(false, "Invalid email or password!");
            }
            
        } else {
            sendJsonResponse(false, "Invalid email or password!");
        }
        
        $user_stmt->close();
        if (isset($log_stmt)) $log_stmt->close();
        if (isset($update_stmt)) $update_stmt->close();
        
    } catch (Exception $e) {
        error_log("User login error: " . $e->getMessage());
        sendJsonResponse(false, "An error occurred. Please try again!");
    }
} else {
    sendJsonResponse(false, "Invalid request method!");
}

$conn->close();
?>
