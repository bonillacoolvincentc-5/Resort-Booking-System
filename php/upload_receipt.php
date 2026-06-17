<?php
/**
 * Upload Payment Receipt
 * Handles GCash receipt image uploads for bookings
 */

session_start();
require_once '../phpAuthentication/db_config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to upload receipts'
    ]);
    exit();
}

try {
    // Check if file was uploaded
    if (!isset($_FILES['receipt']) || $_FILES['receipt']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload error occurred');
    }
    
    $file = $_FILES['receipt'];
    $bookingId = $_POST['booking_id'] ?? null;
    
    if (!$bookingId) {
        throw new Exception('Booking ID is required');
    }
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Invalid file type. Only JPG, PNG, and GIF images are allowed');
    }
    
    // Validate file size (max 5MB)
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        throw new Exception('File size exceeds 5MB limit');
    }
    
    // Verify booking belongs to user
    $stmt = $conn->prepare("SELECT user_id FROM bookings WHERE id = ?");
    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
    
    if (!$booking || $booking['user_id'] != $_SESSION['user_id']) {
        throw new Exception('Booking not found or access denied');
    }
    
    // Create upload directory if it doesn't exist
    $uploadDir = '../uploads/receipts/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'receipt_' . $bookingId . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to move uploaded file');
    }
    
    // Delete existing receipt if any
    $stmt = $conn->prepare("SELECT id, file_path FROM payment_receipts WHERE booking_id = ?");
    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    $result = $stmt->get_result();
    $existingReceipt = $result->fetch_assoc();
    
    if ($existingReceipt) {
        // Delete old file
        if (file_exists($existingReceipt['file_path'])) {
            unlink($existingReceipt['file_path']);
        }
        
        // Delete database record
        $stmt = $conn->prepare("DELETE FROM payment_receipts WHERE id = ?");
        $stmt->bind_param('i', $existingReceipt['id']);
        $stmt->execute();
    }
    
    // Insert receipt record
    $stmt = $conn->prepare("
        INSERT INTO payment_receipts (booking_id, file_name, file_path, file_size, file_type)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('issis', $bookingId, $filename, $filepath, $file['size'], $file['type']);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Receipt uploaded successfully',
        'filename' => $filename,
        'filepath' => $filepath
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
