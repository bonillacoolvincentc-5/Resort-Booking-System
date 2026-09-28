<?php
/**
 * Submit Payment Receipt (User)
 * Handles receipt upload for bookings in 'awaiting_payment' status.
 * Updates booking status to 'payment_verifying'.
 */

session_start();
require_once __DIR__ . '/../phpAuthentication/db_config.php';
require_once __DIR__ . '/../phpAuthentication/blocked_email_helper.php';
require_once __DIR__ . '/check_expired_bookings.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in.']);
    exit();
}

kick_blocked_user_session($conn, true);

$userId = (int)$_SESSION['user_id'];

try {
    $bookingId = intval($_POST['booking_id'] ?? 0);
    if (!$bookingId) {
        throw new Exception('Booking ID is required.');
    }

    // Check booking exists and belongs to this user
    $stmt = $conn->prepare("
        SELECT b.id, b.booking_reference, b.status_id, b.total_amount, b.expires_at, bs.name AS status_name,
               TIMESTAMPDIFF(SECOND, NOW(), b.expires_at) AS seconds_remaining
        FROM bookings b
        JOIN booking_status bs ON b.status_id = bs.id
        WHERE b.id = ? AND b.user_id = ?
    ");
    $stmt->bind_param('ii', $bookingId, $userId);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$booking) {
        throw new Exception('Booking not found or unauthorized.');
    }

    if ($booking['status_name'] !== 'awaiting_payment') {
        throw new Exception('This booking is not currently awaiting payment (current status: ' . ucwords(str_replace('_', ' ', $booking['status_name'])) . ').');
    }

    // Check if expired using MySQL seconds_remaining
    if (!empty($booking['expires_at']) && intval($booking['seconds_remaining']) <= 0) {
        // Trigger auto-cancel
        $conn->query("
            UPDATE bookings b
            INNER JOIN booking_status bs_canc ON bs_canc.name = 'cancelled'
            SET b.status_id = bs_canc.id, b.updated_at = NOW()
            WHERE b.id = $bookingId
        ");
        throw new Exception('The 2-hour payment window for this booking has expired. The booking has been cancelled.');
    }

    // Validate uploaded file
    if (empty($_FILES['receipt']) || $_FILES['receipt']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Please select a valid payment receipt screenshot.');
    }

    $file = $_FILES['receipt'];
    $maxSize = 2097152; // 2 MB
    if ($file['size'] > $maxSize) {
        throw new Exception('Receipt image is too large. Maximum size is 2MB.');
    }

    // MIME verification
    $detectedMime = '';
    if (function_exists('finfo_open')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $detectedMime = $finfo->file($file['tmp_name']);
    } elseif (function_exists('mime_content_type')) {
        $detectedMime = mime_content_type($file['tmp_name']);
    } else {
        $imgInfo = getimagesize($file['tmp_name']);
        if ($imgInfo !== false) {
            $detectedMime = $imgInfo['mime'];
        }
    }

    $allowedMimes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($allowedMimes[$detectedMime])) {
        throw new Exception('Invalid image format. Allowed formats: JPG, PNG, WEBP.');
    }

    $ext = $allowedMimes[$detectedMime];
    $uploadDir = __DIR__ . '/../assets/uploads/payment_proofs/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = 'receipt_' . $bookingId . '_' . time() . '.' . $ext;
    $targetPath = $uploadDir . $filename;
    $dbPath = 'assets/uploads/payment_proofs/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception('Failed to save uploaded receipt image.');
    }

    // Get payment_verifying status ID
    $stStmt = $conn->prepare("SELECT id FROM booking_status WHERE name = 'payment_verifying'");
    $stStmt->execute();
    $statusRow = $stStmt->get_result()->fetch_assoc();
    $stStmt->close();

    if (!$statusRow) {
        throw new Exception('Payment verifying status is not configured.');
    }

    $verifyingStatusId = (int)$statusRow['id'];
    $oldStatusId = (int)$booking['status_id'];

    // Update booking
    $upStmt = $conn->prepare("
        UPDATE bookings 
        SET status_id = ?, 
            payment_proof = ?, 
            updated_at = NOW() 
        WHERE id = ?
    ");
    $upStmt->bind_param('isi', $verifyingStatusId, $dbPath, $bookingId);
    $upStmt->execute();
    $upStmt->close();

    // Insert into payment_receipts table
    $recStmt = $conn->prepare("
        INSERT INTO payment_receipts (booking_id, file_name, file_path, file_size, file_type, uploaded_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $origName = basename($file['name']);
    $fileSize = (int)$file['size'];
    $recStmt->bind_param('issis', $bookingId, $origName, $dbPath, $fileSize, $detectedMime);
    $recStmt->execute();
    $recStmt->close();

    // Log the change
    $logStmt = $conn->prepare("
        INSERT INTO booking_logs (booking_id, old_status_id, new_status_id, changed_by, changed_by_id, notes)
        VALUES (?, ?, ?, 'user', ?, 'Payment receipt submitted by guest for verification')
    ");
    $logStmt->bind_param('iiii', $bookingId, $oldStatusId, $verifyingStatusId, $userId);
    $logStmt->execute();
    $logStmt->close();

    echo json_encode([
        'success' => true,
        'message' => 'Payment receipt uploaded successfully! Admin will verify your payment shortly.',
        'receipt_path' => $dbPath
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
