<?php
/*
 * Login Process
 * YP4K Resort - Booking & Trail Guide System
 */

session_start();
require_once 'db_config.php';
require_once 'blocked_email_helper.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $remember = isset($_POST['remember']);

    // Validate input
    if (empty($email) || empty($password)) {
        header("Location: ../Authentication/login.php?error=" . urlencode("Email and password are required!"));
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../Authentication/login.php?error=" . urlencode("Invalid email format!"));
        exit();
    }

    try {
        if (is_email_blocked($conn, $email)) {
            header("Location: ../Authentication/login.php?error=" . urlencode(blocked_email_message('login')));
            exit();
        }

        // Prepare SQL statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password FROM users WHERE email = ?");
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Password is correct, set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['login_time'] = time();

                // Set remember me cookie if checked
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $cookie_time = time() + (30 * 24 * 60 * 60); // 30 days

                    // Store token in database
                    $update_stmt = $conn->prepare("UPDATE users SET remember_token = ?, token_expiry = FROM_UNIXTIME(?) WHERE id = ?");
                    $update_stmt->bind_param("sii", $token, $cookie_time, $user['id']);
                    $update_stmt->execute();

                    // Set cookie
                    setcookie('remember_token', $token, $cookie_time, '/', '', true, true);
                }

                // Redirect to create booking page after login
                header("Location: ../User_Dashboard/create-booking.php?success=" . urlencode("Login successful! Welcome back."));
                exit();
            } else {
                // Password is incorrect
                header("Location: ../Authentication/login.php?error=" . urlencode("Invalid email or password!"));
                exit();
            }
        } else {
            // User not found
            header("Location: ../Authentication/login.php?error=" . urlencode("Invalid email or password!"));
            exit();
        }

        $stmt->close();

    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        header("Location: ../Authentication/login.php?error=" . urlencode("An error occurred. Please try again!"));
        exit();
    }
} else {
    // Redirect if not POST request
    header("Location: ../Authentication/login.php");
    exit();
}

$conn->close();
?>
