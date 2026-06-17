<?php
/*
 * Simple Test Registration - Debug Version
 * This will help identify the exact issue
 */

// Clean output buffer to prevent any HTML
ob_clean();

// Set JSON header first
header('Content-Type: application/json');

// Disable error display
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    // Test basic functionality
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        // Just return a simple success response for testing
        $response = [
            'success' => true,
            'message' => 'Test successful',
            'test_otp' => '123456',
            'redirect' => '../Authentication/verify.php'
        ];
        
        echo json_encode($response);
        exit();
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'POST method required'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
