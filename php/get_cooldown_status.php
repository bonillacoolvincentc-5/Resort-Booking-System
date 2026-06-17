<?php
/*
 * Get Cooldown Status
 * YP4K Resort - Booking & Trail Guide System
 * Returns cooldown status for OTP resend
 */

// Start output buffering to prevent HTML errors
ob_start();

session_start();
require_once '../phpAuthentication/db_config.php';

header('Content-Type: application/json');

// Get cooldown status from database
$email = $_SESSION['verification_email'] ?? '';

if ($email) {
    $stmt = $GLOBALS['conn']->prepare("
        SELECT created_at, attempts 
        FROM otp_verifications 
        WHERE email = ? AND expires_at > NOW() 
        ORDER BY created_at DESC LIMIT 1
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
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
                'success' => true,
                'cooldown' => $remainingCooldown,
                'can_resend' => false
            ]);
        } else {
            // Clean output buffer
            ob_clean();
            echo json_encode([
                'success' => true,
                'cooldown' => 0,
                'can_resend' => true
            ]);
        }
    } else {
        // Clean output buffer
        ob_clean();
        echo json_encode([
            'success' => true,
            'cooldown' => 0,
            'can_resend' => true
        ]);
    }
    $stmt->close();
} else {
    // Clean output buffer
    ob_clean();
    echo json_encode([
        'success' => true,
        'cooldown' => 0,
        'can_resend' => true
    ]);
}
?>
