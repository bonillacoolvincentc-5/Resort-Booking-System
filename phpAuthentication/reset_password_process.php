<?php
/*
 * Reset Password Process
 * YP4K Resort - Booking & Trail Guide System
 */

session_start();
require_once 'db_config.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resetToken = trim($_POST['resetToken'] ?? '');
    $newPassword = trim($_POST['newPassword'] ?? '');
    $confirmNewPassword = trim($_POST['confirmNewPassword'] ?? '');

    // Validation
    if (empty($resetToken) || empty($newPassword) || empty($confirmNewPassword)) {
        header("Location: ../Authentication/forgot-pass.php?error=" . urlencode("All fields are required!"));
        exit();
    }

    if ($newPassword !== $confirmNewPassword) {
        header("Location: ../Authentication/forgot-pass.php?token=" . urlencode($resetToken) . "&error=" . urlencode("Passwords do not match!"));
        exit();
    }

    if (strlen($newPassword) < 8) {
        header("Location: ../Authentication/forgot-pass.php?token=" . urlencode($resetToken) . "&error=" . urlencode("Password must be at least 8 characters long!"));
        exit();
    }

    try {
        // Verify reset token
        $current_time = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("
            SELECT id, email FROM users 
            WHERE reset_token = ? AND reset_token_expiry > ?
        ");
        $stmt->bind_param("ss", $resetToken, $current_time);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

            // Update password and clear reset token
            $update_stmt = $conn->prepare("
                UPDATE users 
                SET password = ?, reset_token = NULL, reset_token_expiry = NULL 
                WHERE id = ?
            ");
            $update_stmt->bind_param("si", $hashedPassword, $user['id']);

            if ($update_stmt->execute()) {
                header("Location: ../Authentication/login.php?success=" . urlencode("Password reset successful! You can now login with your new password."));
                exit();
            } else {
                throw new Exception("Failed to update password");
            }

            $update_stmt->close();
        } else {
            header("Location: ../Authentication/forgot-pass.php?error=" . urlencode("Invalid or expired reset token!"));
            exit();
        }

        $stmt->close();

    } catch (Exception $e) {
        error_log("Reset password error: " . $e->getMessage());
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
