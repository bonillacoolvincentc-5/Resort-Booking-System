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
require_once '../phpAuthentication/blocked_email_helper.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    try {
        // Get email from POST body (preferred) or session fallback
        $email = trim($_POST['email'] ?? $_SESSION['verification_email'] ?? '');

        // Re-hydrate session so subsequent calls keep working
        if (!empty($email)) {
            $_SESSION['verification_email'] = $email;
        }

        if (empty($email)) {
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'No email found for verification']);
            exit;
        }

        if (is_email_blocked($GLOBALS['conn'], $email)) {
            ob_clean();
            echo json_encode(['success' => false, 'message' => blocked_email_message('register'), 'blocked' => true]);
            exit;
        }
        
        // Check cooldown (120 seconds between resends)
        $cooldown_stmt = $GLOBALS['conn']->prepare("
            SELECT TIMESTAMPDIFF(SECOND, created_at, NOW()) AS time_diff 
            FROM otp_verifications 
            WHERE email = ? 
            ORDER BY created_at DESC LIMIT 1
        ");
        $cooldown_stmt->bind_param("s", $email);
        $cooldown_stmt->execute();
        $result = $cooldown_stmt->get_result();
        
        if ($result->num_rows >= 1) {
            $data = $result->fetch_assoc();
            $timeDiff = (int)($data['time_diff'] ?? 0);
            $cooldownPeriod = 120; // 2 minutes cooldown
            
            if ($timeDiff >= 0 && $timeDiff < $cooldownPeriod) {
                $remainingCooldown = $cooldownPeriod - $timeDiff;
                ob_clean();
                echo json_encode([
                    'success' => false,
                    'message' => "Please wait {$remainingCooldown}s before requesting a new code.",
                    'cooldown' => $remainingCooldown,
                    'error_type' => 'cooldown'
                ]);
                exit;
            }
        }
        $cooldown_stmt->close();
        
        // Generate new OTP
        $newOtp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Recover user_data and user_type from the existing DB record before we delete it
        $sessionUserType = $_SESSION['user_type'] ?? 'user';
        $sessionUserData = $_SESSION['user_data'] ?? '{}';

        $get_old = $GLOBALS['conn']->prepare(
            "SELECT user_type, user_data FROM otp_verifications WHERE email = ? ORDER BY created_at DESC LIMIT 1"
        );
        $get_old->bind_param("s", $email);
        $get_old->execute();
        $old_row = $get_old->get_result()->fetch_assoc();
        $get_old->close();
        if ($old_row) {
            if (!empty($old_row['user_type'])) $sessionUserType = $old_row['user_type'];
            if (!empty($old_row['user_data']) && $old_row['user_data'] !== '{}') $sessionUserData = $old_row['user_data'];
        }

        // Delete ALL old OTP records for this email to avoid any conflicts
        $del = $GLOBALS['conn']->prepare("DELETE FROM otp_verifications WHERE email = ?");
        $del->bind_param("s", $email);
        $del->execute();
        $del->close();

        // Insert a fresh OTP record with 5 minutes expiry based on DB NOW()
        $insert_stmt = $GLOBALS['conn']->prepare(
            "INSERT INTO otp_verifications (email, otp, user_type, user_data, expires_at) VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE))"
        );
        $insert_stmt->bind_param("ssss", $email, $newOtp, $sessionUserType, $sessionUserData);
        $insert_stmt->execute();
        $insert_stmt->close();
        
        // Send email with new OTP
        $emailResult = EmailHelper::sendOTPEmail($email, $newOtp, $sessionUserType);
        
        if ($emailResult['success']) {
            ob_clean();
            echo json_encode([
                'success' => true, 
                'message' => 'New verification code sent to your email',
                'cooldown' => 120
            ]);
        } else {
            ob_clean();
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to send email. Please try again.'
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Resend OTP error: " . $e->getMessage());
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
    }
    
} else {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
