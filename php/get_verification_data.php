<?php
/*
 * Get Verification Data
 * YP4K Resort - Booking & Trail Guide System
 * Returns verification data for current session
 */

// Start output buffering to prevent HTML errors
ob_start();

session_start();
require_once '../phpAuthentication/db_config.php';

header('Content-Type: application/json');

// Get verification data from session or database
$email = $_SESSION['verification_email'] ?? '';
$verification_code = $_SESSION['verification_code'] ?? '';
$user_id = $_SESSION['user_id'] ?? '';
$user_type = $_SESSION['user_type'] ?? '';

if ($email) {
    // Clean output buffer
    ob_clean();
    echo json_encode([
        'success' => true,
        'email' => $email,
        'verification_code' => $verification_code,
        'user_id' => $user_id,
        'user_type' => $user_type
    ]);
} else {
    // Try to get from database if no session
    if (isset($_GET['email'])) {
        $email = $_GET['email'];
        
        $stmt = $GLOBALS['conn']->prepare("
            SELECT email, user_type, expires_at 
            FROM otp_verifications 
            WHERE email = ? AND expires_at > NOW() 
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $data = $result->fetch_assoc();
            // Clean output buffer
            ob_clean();
            echo json_encode([
                'success' => true,
                'email' => $data['email'],
                'user_type' => $data['user_type'],
                'expiry' => $data['expires_at']
            ]);
        } else {
            // Clean output buffer
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'No verification data found']);
        }
        $stmt->close();
    } else {
        // Clean output buffer
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'No verification data found']);
    }
}
?>
