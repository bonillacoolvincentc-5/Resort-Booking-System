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
$email = trim($_GET['email'] ?? $_SESSION['verification_email'] ?? '');

if (!empty($email)) {
    $stmt = $GLOBALS['conn']->prepare("
        SELECT TIMESTAMPDIFF(SECOND, created_at, NOW()) AS time_diff 
        FROM otp_verifications 
        WHERE email = ? 
        ORDER BY created_at DESC LIMIT 1
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows >= 1) {
        $data = $result->fetch_assoc();
        $timeDiff = (int)($data['time_diff'] ?? 9999);
        $cooldownPeriod = 120; // 2 minutes cooldown
        
        if ($timeDiff >= 0 && $timeDiff < $cooldownPeriod) {
            $remainingCooldown = $cooldownPeriod - $timeDiff;
            ob_clean();
            echo json_encode([
                'success' => true,
                'cooldown' => $remainingCooldown,
                'can_resend' => false
            ]);
            $stmt->close();
            exit;
        }
    }
    $stmt->close();
}

ob_clean();
echo json_encode([
    'success' => true,
    'cooldown' => 0,
    'can_resend' => true
]);
?>
