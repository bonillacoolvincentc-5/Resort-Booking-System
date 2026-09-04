<?php
/**
 * Send Arrival Reminder Email to Guest
 */

session_start();
require_once '../phpAuthentication/db_config.php';
require_once '../phpAuthentication/email_helper.php';

header('Content-Type: application/json');

// Guard: admin only
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    if (!isset($_SESSION['admin_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized: Admin login required'
        ]);
        exit();
    }
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        throw new Exception('Invalid request data');
    }

    $bookingId = intval($data['booking_id'] ?? 0);
    if (!$bookingId) {
        throw new Exception('Booking ID is required');
    }

    global $conn;

    // Fetch booking details
    $query = "
        SELECT 
            b.booking_reference,
            b.check_in_date,
            b.check_in_time,
            b.full_name,
            COALESCE(b.email, u.email) as guest_email,
            bs.name as status_name,
            CASE 
                WHEN b.booking_type = 'activity' THEN a.name
                WHEN b.booking_type = 'accommodation' THEN acc.name
                WHEN b.booking_type = 'bundle' THEN bun.name
                ELSE acc.name
            END as item_name
        FROM bookings b
        LEFT JOIN booking_status bs ON b.status_id = bs.id
        LEFT JOIN users u ON b.user_id = u.id
        LEFT JOIN activities a ON b.activity_id = a.id
        LEFT JOIN accommodations acc ON b.accommodation_id = acc.id
        LEFT JOIN bundles bun ON b.bundle_id = bun.id
        WHERE b.id = ?
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Booking not found');
    }
    
    $booking = $result->fetch_assoc();
    
    // Safety check - we only want to remind approved bookings
    if ($booking['status_name'] !== 'approved') {
        throw new Exception('Only approved bookings can receive arrival reminders');
    }
    
    if (empty($booking['guest_email'])) {
        throw new Exception('No email address found for this guest');
    }
    
    // Format dates nicely
    $formattedDate = date('F j, Y', strtotime($booking['check_in_date']));
    $formattedTime = $booking['check_in_time'] ? date('g:i A', strtotime($booking['check_in_time'])) : 'Scheduled Time';
    
    // Send email
    $emailResult = EmailHelper::sendArrivalReminderEmail(
        $booking['guest_email'],
        $booking['full_name'],
        $booking['booking_reference'],
        $booking['item_name'],
        $formattedDate,
        $formattedTime
    );
    
    if (!$emailResult['success']) {
        throw new Exception($emailResult['message']);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Reminder email sent successfully.'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
