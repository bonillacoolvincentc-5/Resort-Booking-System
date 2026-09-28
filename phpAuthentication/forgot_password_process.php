<?php
/*
 * Forgot Password Process
 * YP4K Resort - Booking & Trail Guide System
 */

session_start();
require_once 'db_config.php';
require_once 'blocked_email_helper.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    // Validate email
    if (empty($email)) {
        header("Location: ../Authentication/forgot-pass.php?error=" . urlencode("Email is required!"));
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../Authentication/forgot-pass.php?error=" . urlencode("Invalid email format!"));
        exit();
    }

    try {
        if (is_email_blocked($conn, $email)) {
            header("Location: ../Authentication/forgot-pass.php?error=" . urlencode(blocked_email_message('forgot')));
            exit();
        }

        // Check if email exists in database
        $stmt = $conn->prepare("SELECT id, email, firstName FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Generate reset token
            $reset_token = bin2hex(random_bytes(32));
            $token_expiry = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiry

            // Update user with reset token
            $update_stmt = $conn->prepare("
                UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?
            ");
            $update_stmt->bind_param("ssi", $reset_token, $token_expiry, $user['id']);

            if ($update_stmt->execute()) {
                // In a real application, send email with reset link
                // sendPasswordResetEmail($user['email'], $reset_token, $user['firstName']);

                // For demonstration, show the reset link on success page
                $reset_link = "../Authentication/forgot-pass.php?token=" . urlencode($reset_token);

                // Log the reset link (for development purposes only - remove in production)
                error_log("Password reset link for {$email}: {$reset_link}");

                header("Location: ../Authentication/forgot-pass.php?success=" . urlencode("Password reset link sent to your email! Check your inbox."));
                exit();
            } else {
                throw new Exception("Failed to update reset token");
            }

            $update_stmt->close();
        } else {
            // For security, don't reveal if email exists or not
            header("Location: ../Authentication/forgot-pass.php?success=" . urlencode("If an account exists with this email, you will receive a password reset link."));
            exit();
        }

        $stmt->close();

    } catch (Exception $e) {
        error_log("Forgot password error: " . $e->getMessage());
        header("Location: ../Authentication/forgot-pass.php?error=" . urlencode("An error occurred. Please try again!"));
        exit();
    }
} else {
    // Redirect if not POST request
    header("Location: ../Authentication/forgot-pass.php");
    exit();
}

$conn->close();
?>
