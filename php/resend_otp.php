<?php
/*
 * Resend OTP
 * YP4K Resort - Booking & Trail Guide System
 * Handles OTP resend functionality
 */

// Start output buffering to prevent HTML errors
ob_start();

session_start();
require_once '../phpAuthentication/db_config.php';
require_once '../phpAuthentication/email_helper.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    try {
        // Get email from session or request
        $email = $_SESSION['verification_email'] ?? '';
        
        if (empty($email)) {
            // Clean output buffer
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'No email found for verification']);
            exit;
        }
        
        // Check cooldown (1 minute between resends)
        $cooldown_stmt = $GLOBALS['conn']->prepare("
            SELECT created_at, attempts 
            FROM otp_verifications 
            WHERE email = ? AND expires_at > NOW() 
            ORDER BY created_at DESC LIMIT 1
        ");
        $cooldown_stmt->bind_param("s", $email);
        $cooldown_stmt->execute();
        $result = $cooldown_stmt->get_result();
        
        if ($result->num_rows === 1) {
            $data = $result->fetch_assoc();
            $lastAttemptTime = strtotime($data['created_at']);
            $currentTime = time();
            $timeDiff = $currentTime - $lastAttemptTime;
            $cooldownPeriod = 60; // 1 minute cooldown
            
            if ($timeDiff < $cooldownPeriod) {
                $remainingCooldown = $cooldownPeriod - $timeDiff;
                // Clean output buffer
                ob_clean();
                echo json_encode([
                    'success' => false,
                    'message' => "Resend available in {$remainingCooldown}s",
                    'cooldown' => $remainingCooldown,
                    'error_type' => 'cooldown'
                ]);
                exit;
            }
        }
        
        // Generate new OTP
        $newOtp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $otpExpiry = date('Y-m-d H:i:s', time() + 300); // 5 minutes
        
        // Update existing OTP record
        $update_stmt = $GLOBALS['conn']->prepare("
            UPDATE otp_verifications 
            SET otp = ?, created_at = NOW(), expires_at = ?, attempts = 0 
            WHERE email = ? AND expires_at > NOW()
        ");
        $update_stmt->bind_param("sss", $newOtp, $otpExpiry, $email);
        $update_stmt->execute();
        
        if ($update_stmt->affected_rows === 0) {
            // Create new OTP record if update failed (preserve original user data/type)
            $sessionUserType = $_SESSION['user_type'] ?? 'user';
            $sessionUserData = $_SESSION['user_data'] ?? '{}';
            $insert_stmt = $GLOBALS['conn']->prepare("
                INSERT INTO otp_verifications (email, otp, user_type, user_data, expires_at) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $insert_stmt->bind_param("sssss", $email, $newOtp, $sessionUserType, $sessionUserData, $otpExpiry);
            $insert_stmt->execute();
            $insert_stmt->close();
        }
        
        // Send email with new OTP
        $emailResult = EmailHelper::sendOTPEmail($email, $newOtp, 'user');
        
        if ($emailResult['success']) {
            // Clean output buffer
            ob_clean();
            echo json_encode([
                'success' => true, 
                'message' => 'New verification code sent to your email'
            ]);
        } else {
            // Clean output buffer
            ob_clean();
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to send email. Please try again.'
            ]);
        }
        
        $update_stmt->close();
        $cooldown_stmt->close();
        
    } catch (Exception $e) {
        error_log("Resend OTP error: " . $e->getMessage());
        // Clean output buffer
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
    }
    
} else {
    // Clean output buffer
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
